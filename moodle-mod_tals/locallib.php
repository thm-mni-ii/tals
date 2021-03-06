<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Provides functionality used by local applications.
 *
 * @package     mod_tals
 * @copyright   2017 Technische Hochschule Mittelhessen - University of Applied Sciences - Giessen, Germany
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/../../lib/externallib.php');
require_once(dirname(__FILE__) . '/../../lib/enrollib.php');

define("SUCCESS", 0);
define("FAIL", -1);
define("WARNING_NO_UPDATE", -2);
define("ERROR", -3);
define("ERROR_NO_LOG", -4);
define("ERROR_NO_ATT_TYPE", -5);
define("ERROR_NO_APPOINTMENT", -6);
define("ERROR_PIN_WRONG", -7);
define("ERROR_PIN_DISABLED", -8);
define("ERROR_NO_PIN", -9);

define("PRESENT", 1);
define("ABSENT", 2);
define("EXCUSED", 3);
define("INTERNAL", 1);
define("VPN", 2);
define("EXTERNAL", 3);

/**
 * Generates a random 4-digit PIN
 * @param none
 * @return int
 */
function tals_generate_pin() {
    return mt_rand(1000, 9999);
}

/**
 * Get the token of calling user
 * @param none
 * @return stdClass token object
 */
function tals_get_token() {
    global $DB;

    $service = $DB->get_record('external_services', ['name' => get_string('talsname', 'tals'), 'component' => 'mod_tals']);

    // From lib/externallib.php.
    $token = external_generate_token_for_current_user($service);

    return $token;
}

/**
 * Checks if the given IP is in given Network
 * @param  ip - IP to check in IPV4 format eg. 127.0.0.1
 * @param  range - IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
 * @return if on range true, otherwise false
 */
function tals_check_ip_range($ip, $range) {
    if (!strpos($range, '/')) {
        $range .= '/32';
    }

    list($range, $netmask) = explode('/', $range, 2);

    $rangedecimal = ip2long($range);
    $ipdecimal = ip2long($ip);
    $wildcarddecimal = pow(2, (32 - $netmask)) - 1;
    $netmaskdecimal = ~$wildcarddecimal;

    return (($ipdecimal & $netmaskdecimal) == ($rangedecimal & $netmaskdecimal));
}

/**
 * Determines the IP of current request and returns the acceptance.
 * @return 1 if estimated, 2 if ok, 3 otherwise (trafficlights principle)
 */
function tals_get_type_net() {
    global $DB;

    $ipaddress = $_SERVER['REMOTE_ADDR'];
    $defaultacceptance = 3;

    $netdefstring = '{"wlan-gi": {
                        "10.192.0.0/16":1
                      },
                      "vpn-gi": {
                        "10.196.48.0/24":2,
                        "10.196.49.0/24":2
                      },
                      "vpn-fb": {
                        "212.201.31.80/32":2
                      },
                      "eduroam-gi": {
                        "10.192.0.0/16":1
                      }
                    }';
    $netdefjson = json_decode($netdefstring, true);

    foreach ($netdefjson as $entry) {
        foreach ($entry as $iprange => $acceptance) {
            if (tals_check_ip_range($ipaddress, $iprange)) {
                return $acceptance;
            }
        }
    }

    return $defaultacceptance;
}

/**
 * Returns a list of courses. Checks implicit if the user is enrolled in course.
 * @param userid - id of user you wish to get a courselist for
 * @param courseid - id of course you wish to get info for
 * @return array
 */
function tals_get_courses($userid, $courseid = null) {
    global $DB;

    $talscourses = $DB->get_records('tals', [], null, 'course');
    $allcourses = enrol_get_all_users_courses($userid, true);
    $courses = [];

    // Compose list of courses the user is enrolled in and tals is used.
    foreach ($allcourses as $course) {
        foreach ($talscourses as $tals) {
            if ($tals->course == $course->id) {
                $tmpcourse = new stdClass;
                $tmpcourse->id = $course->id;
                $tmpcourse->shortname = $course->shortname;
                $tmpcourse->fullname = $course->fullname;
                $tmpcourse->startdate = $course->startdate;

                array_push($courses, $tmpcourse);
            }
        }
    }

    if (!is_null($courseid)) {
        $result = [];

        foreach ($courses as $course) {
            if ($course->id == $courseid) {
                array_push($result, $course);
                break;
            }
        }

        $courses = $result;
    }

    return $courses;
}

/**
 * Deletes a pin from the database.
 * @param pin - used pin
 * @return None
 */
function tals_delete_pin($pinid) {
    global $DB;

    return $DB->delete_records('tals_pin', ['id' => $pinid]);
}

/**
 * Inserts a pin into the database
 * @param pindur - the duration of the pin
 * @return id of the new pin
 */
function tals_insert_pin($pindur) {
    global $DB;

    $pin = new stdClass;

    $pin->pin = tals_generate_pin();
    $pin->duration = $pindur;
    $pin->id = $DB->insert_record('tals_pin', $pin, true);

    return $pin->id;
}

/**
 * Updates a pin in the database.
 * @param pinid - id of the pin to be updated (if null, a new PIN is generated)
 * @param duration - how many minutes pin will be active
 * @return id of the pin
 */
function tals_update_pin($pinid = null, $pindur) {
    global $DB;

    if (is_null($pinid)) {
        return tals_insert_pin($pindur);
    }

    if (!$DB->record_exists('tals_pin', ['id' => $pinid])) {
        return tals_insert_pin($pindur);
    }

    $pin = $DB->get_record('tals_pin', ['id' => $pinid]);

    $pin->duration = $pindur;

    $DB->update_record('tals_pin', $pin);

    return $pin->id;
}

/**
 * Enables an existing pin.
 * @param appid - id of the appointment with pin to be activated
 * @return if successfull duration of pin, otherwise negative for some error occured
 */
function tals_enable_pin($appid) {
    global $DB;

    if (!$DB->record_exists('tals_appointment', ['id' => $appid])) {
        return ERROR_NO_APPOINTMENT;
    }

    $appointment = $DB->get_record('tals_appointment', ['id' => $appid]);

    if (is_null($appointment->fk_pin_id)) {
        return ERROR_NO_PIN;
    }

    $now = strtotime(date('d-m-Y H:i:s', time()));

    if (($now < $appointment->start) || ($now > $appointment->ending)) {
        return ERROR;
    }

    $pin = $DB->get_record('tals_pin', ['id' => $appointment->fk_pin_id]);



    $pin->until = strtotime('+' . $pin->duration . ' minutes', $now);

    $DB->update_record('tals_pin', $pin);

    return $pin->until;
}

/**
 * Checks if a PIN for a given appointment is enabled
 * @param appid - ID of the appointment to check for PIN
 * @return boolean - true, if PIN is enabled, otherwise false
 */
function tals_check_for_enabled_pin($appid) {
    global $DB;

    $appointment = $DB->get_record('tals_appointment', ['id' => $appid]);

    if (!is_null($appointment->fk_pin_id)) {
        $pin = $DB->get_record('tals_pin', ['id' => $appointment->fk_pin_id]);

        if (!is_null($pin->until)) {
            $now = strtotime(date('d-m-Y H:i', time()));

            if ($now < $pin->until) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Deletes an appointment from the database.
 * @param appid - id of the appointment
 */
function tals_delete_appointment($appid) {
    global $DB;

    $retVal = new stdClass();
    $retVal->deletedLog = false;
    $retVal->deletedAppointment = false;
    $retVal->hadPin = false;
    $retVal->deletedPin = false;
    if ($DB->record_exists('tals_appointment', ['id' => $appid])) {
        $appointment = $DB->get_record('tals_appointment', ['id' => $appid]);

        if (!is_null($appointment->fk_pin_id)) {
            $retVal->hadPin = true;
            $retVal->deletedPin = tals_delete_pin($appointment->fk_pin_id);
        }
        $retVal->deletedLog = $DB->delete_records('tals_log', ['fk_appointment_id' => $appointment->id, 'courseid' => $appointment->courseid]);
        $retVal->deletedAppointment = $DB->delete_records('tals_appointment', ['id' => $appointment->id, 'courseid' => $appointment->courseid]);
    }
    return $retVal;
}

/**
 * Inserts an appointment into the database.
 * @param title - title of the appointment
 * @param start - begin of appointment as timestamp (date and time)
 * @param ending - end of the appointment as timestamp (date and time)
 * @param description - description of the appointment
 * @param courseid - id of course the appointment belongs to
 * @param groupid - id of group which this appointment is associated with
 * @param apptype - type of the appointment accordingly to tals_type_appointment
 * @param pinum - pin for the appointment
 * @param pindur - duration for the pin
 * @return id of appointment
 */
function tals_insert_appointment($title, $start, $ending, $description, $courseid, $groupid, $apptype, $pinum = false, $pindur = 5) {
    global $DB;

    $pinid = null;

    if ($pinum) {
        $pinid = tals_update_pin(null, $pindur);
    }

    $appointment = new stdClass;

    $appointment->title = $title;
    $appointment->start = $start;
    $appointment->ending = $ending;
    $appointment->description = $description;
    $appointment->courseid = $courseid;
    $appointment->groupid = $groupid;
    $appointment->fk_type_appointment_id = $apptype;
    $appointment->fk_pin_id = $pinid;

    $appointment->id = $DB->insert_record('tals_appointment', $appointment);

    return $appointment->id;
}

/**
 * Updates an appointment in the database.
 * @param appid - id of the appointment
 * @param title - title of the appointment
 * @param start - begin of appointment as timestamp (date and time)
 * @param ending - end of the appointment as timestamp (date and time)
 * @param description - description of the appointment
 * @param courseid - id of course the appointment belongs to
 * @param groupid - id of group which this appointment is associated with
 * @param apptype - type of the appointment accordingly to tals_type_appointment
 * @param pin - pin for the appointment
 * @param pindur - duration for the pin
 * @return None
 */
function tals_update_appointment($appid = null, $title, $start, $ending, $description, $courseid,
                                 $groupid, $apptype, $pinum = false, $pindur = 5) {
    global $DB;

    if (is_null($appid) || !$DB->record_exists('tals_appointment', ['id' => $appid])) {
        return tals_insert_appointment($title, $start, $ending, $description, $courseid, $groupid, $apptype, $pinum, $pindur);
    }

    $appointment = $DB->get_record('tals_appointment', ['id' => $appid, 'courseid' => $courseid]);
    $pinid = null;

    if (is_null($appointment->fk_pin_id) && $pinum) {
        $pinid = tals_update_pin(null, $pindur);
    } else if (!is_null($appointment->fk_pin_id) && !$pinum) {
        tals_delete_pin($appointment->fk_pin_id);
    } else if (!is_null($appointment->fk_pin_id) && $pinum) {
        $pinid = tals_update_pin($appointment->fk_pin_id, $pindur);
    }

    $appointment->title = $title;
    $appointment->start = $start;
    $appointment->ending = $ending;
    $appointment->description = $description;
    $appointment->fk_type_appointment_id = $apptype;
    $appointment->fk_pin_id = $pinid;

    $DB->update_record('tals_appointment', $appointment);

    return $appointment->id;
}

/**
 * Enters the attendance of a single user into the database
 * @param userid - id of the user
 * @param comment - optional comment
 * @param typeatt - type of the attendance
 * @param typenet - type of the net where the request come from
 * @param appid - if od the appointent
 * @param pinum - entered pin
 * @return if fail: some error string, if success: some success string
 */
function tals_insert_attendance($userid, $comment = "", $typeatt, $acceptance, $appid, $pinum = null, $isadmin = false) {
    global $DB;

    if (!$DB->record_exists('tals_appointment', ['id' => $appid])) {
        return ERROR_NO_APPOINTMENT;
    }

    $pinok = false;

    $appointment = $DB->get_record('tals_appointment', ['id' => $appid]);

    if (!$isadmin) {
        if (is_null($pinum)) {
            return ERROR;
        }

        if (!is_numeric($pinum)) {
            return ERROR;
        }

        if (tals_check_for_enabled_pin($appointment->id)) {

            $pin = $DB->get_record('tals_pin', ['id' => $appointment->fk_pin_id]);

            if ($pin->pin == $pinum) {
                $pinok = true;
            } else {
                return ERROR_PIN_WRONG;
            }
        } else {
            return ERROR_PIN_DISABLED;
        }
    }

    if (!$DB->record_exists('tals_type_attendance', ['id' => $typeatt])) {
        return ERROR_NO_ATT_TYPE;
    }

    $log = new stdClass;

    $log->userid = $userid;
    $log->comment = $comment;
    $log->courseid = $appointment->courseid;
    $log->fk_type_attendance_id = $typeatt;
    $log->fk_type_net_id = $acceptance;
    $log->fk_appointment_id = $appointment->id;

    $log->id = $DB->insert_record('tals_log', $log, true);

    return $log->id;
}

/**
 * Updates a single attendance of a single user in the database.
 * @param userid - id of the user
 * @param comment - optional comment
 * @param typeatt - type of the attendance
 * @param appid - if od the appointent
 * @return if fail: some error string, if success: some success string
 */
function tals_update_attendance($userid, $comment = "", $typeatt, $acceptance = 1, $appid, $pinum = null, $isadmin = false) {
    global $DB;

    if (!$DB->record_exists('tals_log', ['userid' => $userid, 'fk_appointment_id' => $appid])) {
        return tals_insert_attendance($userid, $comment, $typeatt, $acceptance, $appid, $pinum, $isadmin);
    }

    if (!$DB->record_exists('tals_type_attendance', ['id' => $typeatt])) {
        return ERROR_NO_ATT_TYPE;
    }

    $log = $DB->get_record('tals_log', ['userid' => $userid, 'fk_appointment_id' => $appid]);

    $log->fk_type_attendance_id = $typeatt;
    $log->comment = $comment;

    $DB->update_record('tals_log', $log);

    return $log->id;
}

/**
 * Takes an appointment-Object and resolves dependencies
 * @param appointment - an object
 * @return stdClass-Object, same content as appointment but better formated
 */
function tals_format_appointment($appointment) {
    global $DB;

    $result = new stdClass;

    $result->id = $appointment->id;
    $result->title = $appointment->title;
    $result->description = $appointment->description;
    $result->start = $appointment->start;
    $result->ending = $appointment->ending;
    $result->duration = round(abs($appointment->ending - $appointment->start) / 60, 2);
    $result->courseid = $appointment->courseid;
    $type = $DB->get_record('tals_type_appointment', ['id' => $appointment->fk_type_appointment_id], 'title');
    $result->type = $type->title;
    $result->groupid = $appointment->groupid;

    if (!is_null($appointment->fk_pin_id)) {
        $pin = $DB->get_record('tals_pin', ['id' => $appointment->fk_pin_id]);
        $result->pin = $pin->pin;
        $result->pindur = $pin->duration;
        $result->pinuntil = $pin->until;
    } else {
        $result->pin = null;
        $result->pindur = null;
        $result->pinuntil = null;
    }

    return $result;
}

/**
 * Returns a single appointment in well-formated style
 * @param appid - the appointments id
 * @return stdClass-Object of NULL if appid not existent
 */
function tals_get_single_appointment($appid) {
    global $DB;

    $appointment = $DB->get_record('tals_appointment', ['id' => $appid]);

    if (empty($appointment)) {
        return null;
    }

    return tals_format_appointment($appointment);
}

/**
 * Returns a set of appointments in the specified time period.
 * @param courseid - course which hosts these appointments (optional)
 * @param start - startdate of the appointment
 * @param end - enddate of the appointment
 * @return a set of appointments, might be empty if no matching appointments found
 */
function tals_get_appointments($courseid = null, $start, $end) {
    global $DB;

    $result = [];
    $where = 'start >= ' . $start . ' AND ending <=' . $end;

    if (!is_null($courseid)) {
        $where .= ' AND courseid = ' . $courseid;
    }

    $list = $DB->get_records_select('tals_appointment', $where);

    foreach ($list as $entry) {
        array_push($result, tals_format_appointment($entry));
    }

    return $result;
}

/**
 * Returns a set of appointments happen now.
 * @param courseid - course which hosts these appointments
 * @return a set of appointments, might be empty if no matching appointments found
 */
function tals_get_current_appointments($courseid) {
    global $DB;

    $now = strtotime(date('d-m-Y H:i', time()));
    $result = [];

    $where = 'courseid = ' . $courseid . ' AND start <= ' . $now . ' AND ending >= ' . $now;

    $list = $DB->get_records_select('tals_appointment', $where);

    foreach ($list as $entry) {
        array_push($result, tals_format_appointment($entry));
    }

    return $result;
}

/**
 * Returns a list of all appointments of given course
 * @param courseid - id of course to get the appointments from
 * @return array
 */
function tals_get_all_appointments_of_course($courseid) {
    global $DB;

    $result = [];
    $list = $DB->get_records('tals_appointment', ['courseid' => $courseid]);

    foreach ($list as $entry) {
        array_push($result, tals_format_appointment($entry));
    }

    return $result;
}

/**
 * Returns the appointment next to now.
 * @param courseid - course which hosts these appointments
 * @return stdClass-Object holding an appointment
 */
function tals_get_next_appointment($courseid) {
    global $DB;

    $now = strtotime(date('d-m-Y H:i', time()));

    $appointment = $DB->get_record_sql('SELECT * FROM {tals_appointment} WHERE courseid = ' . $courseid
        . ' AND start > ' . $now . ' ORDER BY start LIMIT 1');

    if (empty($appointment)) {
        return null;
    }

    return tals_format_appointment($appointment);
}

/**
 * Returns count of appointments a user missed.
 * @param userid - id of user to look for
 * @param courseid - id of course to shrink the result
 * @return int
 */
function tals_get_attendance_count_for_user($userid, $courseid) {
    global $DB;

    $status = new stdClass;

    $status->present = 0;
    $status->absent = 0;
    $status->excused = 0;

    $now = strtotime(date('d-m-Y H:i', time()));
    $where = 'courseid = ' . $courseid . ' AND ending < ' . $now;

    // Step 0: Get a list of all logs for this user in this course.
    // Get a list of all appointments of this course which finished yet.
    $logs = $DB->get_records('tals_log', ['userid' => $userid, 'courseid' => $courseid]);
    $appointments = $DB->get_records_select('tals_appointment', $where);

    // Step 1: Split list of appointments into list with groups and list without groups.
    $nogroup = [];
    $groups = [];
    $grouplist = [];

    foreach ($appointments as $entry) {
        if (in_array($entry->groupid, $grouplist)) {
            continue;
        }

        $groupcount = $DB->count_records('tals_appointment', ['groupid' => $entry->groupid]);

        if ($groupcount > 1) {
            $innerwhere = 'courseid = ' . $courseid . ' AND groupid = ' . $entry->groupid . ' AND ending < ' . $now;
            $groupcountended = $DB->count_records_select('tals_appointment', $innerwhere);

            if ($groupcount == $groupcountended) {
                $appgroup = $DB->get_records('tals_appointment', ['groupid' => $entry->groupid, 'courseid' => $entry->courseid]);
                array_push($groups, $appgroup);
                array_push($grouplist, $entry->groupid);
            }
        } else {
            array_push($nogroup, $entry);
        }
    }

    // Step 2: Go through all appointments without groups and count how many the user missed.
    foreach ($nogroup as $entry) {
        $att = ABSENT;

        // If there is no PIN, this appointment is not compulsory. Therefore, a student can't miss/attend it.
        if (is_null($entry->fk_pin_id)) {
            continue;
        }

        foreach ($logs as $log) {
            if ($entry->id == $log->fk_appointment_id) {
                $att = $log->fk_type_attendance_id;
                break;
            }
        }

        if ($att == PRESENT) {
            $status->present++;
        } else if ($att == ABSENT) {
            $status->absent++;
        } else if ($att == EXCUSED) {
            $status->excused++;
        }
    }

    // Step 3: Go through appointments with groups and count how many groups the user missed.
    foreach ($groups as $innergroup) {
        $att = new stdClass;
        $att->present = 0;
        $att->absent = 0;
        $att->excused = 0;
        $found = false;

        foreach ($innergroup as $entry) {
            // If there is no PIN, this appointment is not compulsory. Therefore, a student can't miss/attend it.
            if (is_null($entry->fk_pin_id)) {
                continue;
            }

            foreach ($logs as $log) {
                if ($entry->id == $log->fk_appointment_id) {
                    if ($log->fk_type_attendance_id == PRESENT) {
                        $att->present++;
                    } else if ($log->fk_type_attendance_id == ABSENT) {
                        $att->absent++;
                    } else if ($log->fk_type_attendance_id == EXCUSED) {
                        $att->excused++;
                    }

                    $found = true;
                }
            }
        }

        if ($found) {
            if ($att->present > 0) {
                $status->present++;
            } else if ($att->absent > 0) {
                $status->absent++;
            } else if ($att->excused > 0) {
                $status->excused++;
            }
        } else {
            $status->absent++;
        }
    }

    // Step 4: Return the count.
    return $status;
}

/**
 * Returns if a user is already attending on an appointment
 * @param appid - id of the appointment
 * @param userid - id of the user
 * @return true, if user is already attending, otherwise false
 */
function tals_is_user_already_attending($appid, $userid) {
    global $DB;

    $where = 'fk_type_attendance_id != ' . ABSENT . ' AND fk_appointment_id = ' . $appid . ' AND userid = ' . $userid;
    return $DB->record_exists_select('tals_log', $where);
}

/**
 * Returns logs for given course and appointment.
 * @param courseid - id of course the appointment belongs to
 * @param appointmentid - id of appointment the log belongs to
 * @param typeatt - id of attendance type you wish to filter for (optional)
 * @return array
 */
function tals_get_logs_for_course($courseid, $appointmentid, $typeatt = null) {
    global $DB;

    $result = [];

    if (is_null($typeatt)) {
        $result = $DB->get_records('tals_log', ['courseid' => $courseid, 'fk_appointment_id' => $appointmentid]);
    } else {
        $result = $DB->get_records('tals_log', ['courseid' => $courseid, 'fk_appointment_id' => $appointmentid,
            'fk_type_attendance_id' => $typeatt]);
    }

    return $result;
}

/**
 * Returns the greater of two objects, used comparison by attendance and lastname
 * @param a - first object
 * @param b - second object
 * @return stdClass-Object
 */
function tals_sort_by_attendance($a, $b) {
    if ($a->attendance == $b->attendance) {
        return $a->lastname > $b->lastname;
    } else {
        return $a->attendance > $b->attendance;
    }
}

/**
 * Returns list of users of given course which attended on given appointment.
 * @param courseid - id of course the users are enrolled in
 * @param appointmentid - id of appointment you wish the logs for
 * @return array
 */
function tals_get_attendance_report_for_appointment($courseid, $appointmentid) {
    global $DB;

    $context = context_course::instance($courseid);

    $users = get_enrolled_users($context, '', 0, 'u.id, u.firstname, u.lastname, u.email', null, 0, 0, true);

    $logs = $DB->get_records('tals_log', ['fk_appointment_id' => $appointmentid, 'courseid' => $courseid]);

    $result = [];

    foreach ($users as $user) {
        $found = false;
        $tmp = new stdClass;

        $tmp->userid = $user->id;
        $tmp->firstname = $user->firstname;
        $tmp->lastname = $user->lastname;
        $tmp->email = $user->email;

        foreach ($logs as $log) {
            if ($user->id == $log->userid) {
                $tmp->attendance = $log->fk_type_attendance_id;
                $tmp->acceptance = $log->fk_type_net_id;
                $tmp->comment = $log->comment;

                $found = true;
                break;
            }
        }

        if (!$found) {
            $tmp->attendance = ABSENT;
            $tmp->acceptance = EXTERNAL;
            $tmp->comment = "";
        }

        array_push($result, $tmp);
    }

    // Sort the result-list by lastname and attendance.
    usort($result, "tals_sort_by_attendance");

    return $result;
}

/**
 * Returns Profile of user in course
 * @param userid - id of user to build the profile from
 * @param courseid - id of course the user is in
 * @return array
 */
function tals_get_user_profile_for_course($userid, $courseid) {
    global $DB;

    $profile = new stdClass;
    $user = $DB->get_record('user', ['id' => $userid], 'id, username, firstname, lastname, email');

    $profile->id = $user->id;
    $profile->username = $user->username;
    $profile->firstname = $user->firstname;
    $profile->lastname = $user->lastname;
    $profile->email = $user->email;

    $profile->status = tals_get_attendance_count_for_user($user->id, $courseid);

    $logs = $DB->get_records('tals_log', ['userid' => $user->id, 'courseid' => $courseid]);

    $profile->countappointments = $DB->count_records('tals_appointment', ['courseid' => $courseid]);
    $profile->countcompulsory = $DB->count_records_sql(
        "SELECT COUNT(DISTINCT groupid) FROM mdl_tals_appointment WHERE fk_pin_id IS NOT NULL AND courseid = ?",
        [$courseid]
    );

    $now = strtotime(date('d-m-Y H:i', time()));
    $where = 'courseid = ' . $courseid . ' AND ending < ' . $now;
    $courseapps = $DB->get_records_select('tals_appointment', $where);
    $userapps = [];

    foreach ($courseapps as $entry) {
        // If there is no PIN, we don't care about this appointment in the attendance overview.
        if (is_null($entry->fk_pin_id)) {
            continue;
        }

        $found = false;
        $tmp = new stdClass;

        $tmp->id = $entry->id;
        $tmp->title = $entry->title;
        $tmp->description = $entry->description;
        $tmp->start = $entry->start;
        $tmp->ending = $entry->ending;
        $tmp->duration = round(abs($entry->ending - $entry->start) / 60, 2);
        $type = $DB->get_record('tals_type_appointment', ['id' => $entry->fk_type_appointment_id], 'title');
        $tmp->type = $type->title;
        $tmp->groupid = $entry->groupid;

        foreach ($logs as $log) {
            if ($entry->id == $log->fk_appointment_id) {
                $att = $DB->get_record('tals_type_attendance', ['id' => $log->fk_type_attendance_id], 'description');
                $tmp->attendance = $att->description;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $tmp->attendance = get_string('Absent_full', 'tals');
        }

        array_push($userapps, $tmp);
    }

    $profile->userapps = $userapps;

    return $profile;
}

function tals_get_report_for_export($courseid) {
    global $DB;

    $result = [];
    $tmp = [];

    $course = $DB->get_record('course', ['id' => $courseid]);
    $context = context_course::instance($courseid);
    $users = get_enrolled_users($context, '', 0, 'u.id, u.firstname, u.lastname', null, 0, 0, true);
    $countappointments = $DB->count_records('tals_appointment', ['courseid' => $course->id]);
    $where = 'courseid = ' . $course->id . ' AND fk_pin_id IS NOT NULL';
    $countcompulsory = $DB->count_records_select('tals_appointment', $where);

    // Create header with info about the course.
    $tmp = [mb_convert_encoding($course->fullname, 'UTF-16LE', mb_detect_encoding($course->fullname))];
    array_push($result, $tmp);

    // Current date to know, if its up to date or not.
    $now = date('d.m.Y, H:i', time());
    $tmp = [mb_convert_encoding($now, 'UTF-16LE', mb_detect_encoding($now))];
    array_push($result, $tmp);

    // Count of all appointments of this course.
    $tmp = [mb_convert_encoding(get_string('label_countappointments', 'tals'),
        'UTF-16LE', mb_detect_encoding(get_string('label_countappointments', 'tals'))), $countappointments];
    array_push($result, $tmp);

    // Count how many of them are compulsory.
    $tmp = [mb_convert_encoding(get_string('label_compulsory', 'tals'),
        'UTF-16LE', mb_detect_encoding(get_string('label_compulsory', 'tals'))), $countcompulsory];
    array_push($result, $tmp);

    // Headline.
    $tmp = [mb_convert_encoding(get_string('label_name', 'tals'), 'UTF-16LE', mb_detect_encoding(get_string('label_name', 'tals'))),
        mb_convert_encoding(get_string('Present_full', 'tals'), 'UTF-16LE', mb_detect_encoding(get_string('Present_full', 'tals'))),
        mb_convert_encoding(get_string('Absent_full', 'tals'), 'UTF-16LE', mb_detect_encoding(get_string('Absent_full', 'tals'))),
        mb_convert_encoding(get_string('Excused_full', 'tals'),
            'UTF-16LE', mb_detect_encoding(get_string('Excused_full', 'tals')))];
    array_push($result, $tmp);

    // Everything about the users.
    foreach ($users as $user) {
        $fullname = $user->firstname . ' ' . $user->lastname;
        $fullname = mb_convert_encoding($fullname, 'UTF-16LE', mb_detect_encoding($fullname));
        $status = tals_get_attendance_count_for_user($user->id, $course->id);

        $tmp = [$fullname, $status->present, $status->absent, $status->excused];
        array_push($result, $tmp);
    }

    return $result;
}