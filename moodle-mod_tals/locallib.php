<?php
// This file is part of THM Attendance Logging System (TALS)
//
// TALS is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TALS is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TALS. If not, see <http://www.gnu.org/licenses/>.
//
// TALS is part of an educational project and its not meant to work 
// properly or without errors. It might be no example for best practice
// in programming.

/**
 * Provides functionality used by local applications.
 *
 * @package     mod_tals
 * @copyright   2017 Technische Hochschule Mittelhessen - University of Applied Sciences - Giessen, Germany
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__).'/../../lib/externallib.php');
require_once(dirname(__FILE__).'/../../lib/enrollib.php');

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

  $service = $DB->get_record('external_services', array('name' => get_string('talsname', 'tals'), 'component' => 'mod_tals'));

  // lib/externallib.php
  $token = external_generate_token_for_current_user($service);

  return $token;
}

/**
 * Determines the hostname of current request and returns the acceptance.
 * @return 1 if estimated, 2 if ok, 3 otherwise (trafficlights principle)
 */
function tals_get_type_net() {
  global $DB;

  $ipaddress = $_SERVER['REMOTE_ADDR'];
  $hostname = gethostbyaddr($ipaddress);
  $remotereferer = null;
  $hostacceptance = 3;
  $refereracceptance = 3;

  if (isset($_SERVER['HTTP_REFERER'])) {
    $remotereferer = $_SERVER['HTTP_REFERER'];
  }

  /*This approach is used, because a simple $DB->get_record('tals_type_net', array('host' => $hostname), 'acceptance') throws error.
  The given hint to fix this is using sql_compare_text(), but this function is not documented. Therefore it can't be used (I tried, really).*/
  $list = $DB->get_records('tals_type_net');

  foreach ($list as $entry) {
    if (strpos($hostname, $entry->host) !== false) {
      $hostacceptance = $entry->acceptance;
    }

    if (strpos($remotereferer, $entry->host) !== false) {
      $refereracceptance = $entry->acceptance;
    }
  }

  if ($hostacceptance > $refereracceptance) {
    return $hostacceptance;
  } else {
    return $refereracceptance;
  }
}

/**
 * Returns a list of courses. Checks implicit if the user is enrolled in course.
 * @param userid - id of user you wish to get a courselist for
 * @param courseid - id of course you wish to get info for
 * @return array
 */
function tals_get_courses($userid, $courseid=null) {
  global $DB;

  $talscourses = $DB->get_records('tals', array(), null, 'course');
  $allcourses = enrol_get_all_users_courses($userid, true);
  $courses = array();

  // compose list of courses the user is enrolled in and tals is used
  foreach ($allcourses as $course) {
    foreach ($talscourses as $tals) {
      if ($tals->course == $course->id) {
        $tmpCourse = new stdClass;
        $tmpCourse->id = $course->id;
        $tmpCourse->shortname = $course->shortname;
        $tmpCourse->fullname = $course->fullname;
        $tmpCourse->startdate = $course->startdate;

        array_push($courses, $tmpCourse);
      }
    }
  }

  if (!is_null($courseid)) {
    $result = array();

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

  $DB->delete_records('tals_pin', array('id' => $pinid));
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
function tals_update_pin($pinid=null, $pindur) {
  global $DB;

  if (is_null($pinid)) {
    return tals_insert_pin($pindur);
  }

  if (!$DB->record_exists('tals_pin', array('id' => $pinid))) {
    return tals_insert_pin($pindur);
  }

  $pin = $DB->get_record('tals_pin', array('id' => $pinid));

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

  if (!$DB->record_exists('tals_appointment', array('id' => $appid))) {
    return ERROR_NO_APPOINTMENT;
  }

  $appointment = $DB->get_record('tals_appointment', array('id' => $appid));

  if (is_null($appointment->fk_pin_id)) {
    return ERROR_NO_PIN;
  }

  $now = strtotime(date('d-m-Y H:i:s', time()));

  if (($now < $appointment->start) || ($now > $appointment->end)) {
    return ERROR;
  }

  $pin = $DB->get_record('tals_pin', array('id' => $appointment->fk_pin_id));

  $pin->until = strtotime('+'.$pin->duration.' minutes', $now);

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

  $appointment = $DB->get_record('tals_appointment', array('id' => $appid));
  
  if (!is_null($appointment->fk_pin_id)) {
    $pin = $DB->get_record('tals_pin', array('id' => $appointment->fk_pin_id));
    
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

  if ($DB->record_exists('tals_appointment', array('id' => $appid))) {
    $appointment = $DB->get_record('tals_appointment', array('id' => $appid));

    if (!is_null($appointment->fk_pin_id)) { 
      tals_delete_pin($appointment->fk_pin_id);
    }

    $DB->delete_records('tals_log', array('fk_appointment_id' => $appointment->id, 'courseid' => $appointment->courseid));
    $DB->delete_records('tals_appointment', array('id' => $appointment->id, 'courseid' => $appointment->courseid));
  }
}

/**
 * Inserts an appointment into the database.
 * @param title - title of the appointment
 * @param start - begin of appointment as timestamp (date and time) 
 * @param end - end of the appointment as timestamp (date and time)
 * @param description - description of the appointment
 * @param courseid - id of course the appointment belongs to
 * @param groupid - id of group which this appointment is associated with
 * @param apptype - type of the appointment accordingly to tals_type_appointment
 * @param pinum - pin for the appointment
 * @param pindur - duration for the pin
 * @return id of appointment
 */
function tals_insert_appointment($title, $start, $end, $description, $courseid, $groupid, $apptype, $pinum=false, $pindur=5) {
  global $DB;

  $pinid = null;

  if ($pinum) {
    $pinid = tals_update_pin(null, $pindur);
  }

  $appointment = new stdClass;

  $appointment->title = $title;
  $appointment->start = $start;
  $appointment->end = $end;
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
 * @param end - end of the appointment as timestamp (date and time)
 * @param description - description of the appointment
 * @param courseid - id of course the appointment belongs to
 * @param groupid - id of group which this appointment is associated with
 * @param apptype - type of the appointment accordingly to tals_type_appointment
 * @param pin - pin for the appointment
 * @param pindur - duration for the pin
 * @return None
 */
function tals_update_appointment($appid=null, $title, $start, $end, $description, $courseid, $groupid, $apptype, $pinum=false, $pindur=5) {
  global $DB;

  if (is_null($appid) || !$DB->record_exists('tals_appointment', array('id' => $appid))) {
    return tals_insert_appointment($title, $start, $end, $description, $courseid, $groupid, $apptype, $pinum, $pindur);
  }

  $appointment = $DB->get_record('tals_appointment', array('id' => $appid, 'courseid' => $courseid));
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
  $appointment->end = $end;
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
function tals_insert_attendance($userid, $comment="", $typeatt, $acceptance, $appid, $pinum=null, $isadmin=false) {
  global $DB;

  if (!$DB->record_exists('tals_appointment', array('id' => $appid))) {
    return ERROR_NO_APPOINTMENT;
  }

  $pinok = false;

  $appointment = $DB->get_record('tals_appointment', array('id' => $appid));

  if (!$isadmin) {
    if (is_null($pinum)) {
      return ERROR;
    }

    if (!is_numeric($pinum)) {
      return ERROR;
    }

    if (tals_check_for_enabled_pin($appointment->id)) {

      $pin = $DB->get_record('tals_pin', array('id' => $appointment->fk_pin_id));

      if ($pin->pin == $pinum) {
        $pinok = true;
      } else {
        return ERROR_PIN_WRONG;
      }
    } else {
      return ERROR_PIN_DISABLED;
    }
  }

  if (!$DB->record_exists('tals_type_attendance', array('id' => $typeatt))) {
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
function tals_update_attendance($userid, $comment="", $typeatt, $acceptance=1, $appid, $pinum=null, $isadmin=false) {
  global $DB;

  if (!$DB->record_exists('tals_log', array('userid' => $userid, 'fk_appointment_id' => $appid))) {
    return tals_insert_attendance($userid, $comment, $typeatt, $acceptance, $appid, $pinum, $isadmin);
  }

  if (!$DB->record_exists('tals_type_attendance', array('id' => $typeatt))) {
    return ERROR_NO_ATT_TYPE;
  }

  $log = $DB->get_record('tals_log', array('userid' => $userid, 'fk_appointment_id' => $appid));

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
  $result->end = $appointment->end;
  $result->duration = round(abs($appointment->end - $appointment->start) / 60, 2);
  $result->courseid = $appointment->courseid;
  $type = $DB->get_record('tals_type_appointment', array('id' => $appointment->fk_type_appointment_id), 'title');
  $result->type = $type->title;
  $result->groupid = $appointment->groupid;
  
  if (!is_null($appointment->fk_pin_id)) {
    $pin = $DB->get_record('tals_pin', array('id' => $appointment->fk_pin_id));
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

  $appointment = $DB->get_record('tals_appointment', array('id' => $appid));

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
function tals_get_appointments($courseid=null, $start, $end) {
  global $DB;

  $result = array();
  $where = 'start >= '.$start.' AND end <='.$end;

  if (!is_null($courseid)) {
    $where .= ' AND courseid = '.$courseid;
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
  $result = array();
  
  $where = 'courseid = '.$courseid.' AND start <= '.$now.' AND end >= '.$now;

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

  $result = array();
  $list = $DB->get_records('tals_appointment', array('courseid' => $courseid));

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

  $appointment = $DB->get_record_sql('SELECT * FROM {tals_appointment} WHERE courseid = '.$courseid.' AND start > '.$now.' LIMIT 1');

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
  $where = 'courseid = '.$courseid.' AND end < '.$now;

  // step 0: Get a list of all logs for this user in this course.
  //         Get a list of all appointments of this course which finished yet.
  $logs = $DB->get_records('tals_log', array('userid' => $userid, 'courseid' => $courseid));
  $appointments = $DB->get_records_select('tals_appointment', $where);

  // step 1: Split list of appointments into list with groups and list without groups.
  $nogroup = array();
  $groups = array();
  $grouplist = array();

  foreach ($appointments as $entry) {
    if (in_array($entry->groupid, $grouplist)) {
      continue;
    }

    $groupcount = $DB->count_records('tals_appointment', array('groupid' => $entry->groupid));

    if ($groupcount > 1) {
      $innerwhere = 'courseid = '.$courseid.' AND groupid = '.$entry->groupid.' AND end < '.$now;
      $groupcountended = $DB->count_records_select('tals_appointment', $innerwhere);

      if ($groupcount == $groupcountended) {
        $appgroup = $DB->get_records('tals_appointment', array('groupid' => $entry->groupid, 'courseid' => $entry->courseid));
        array_push($groups, $appgroup);
        array_push($grouplist, $entry->groupid);
      }
    } else {
      array_push($nogroup, $entry);
    }
  }

  // step 2: Go through all appointments without groups and count how many the user missed.
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

  // step 3: Go through appointments with groups and count how many groups the user missed.
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

  // step 4: Return the count.
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

  $where = 'fk_type_attendance_id != '.ABSENT.' AND fk_appointment_id = '.$appid.' AND userid = '.$userid;
  return $DB->record_exists_select('tals_log', $where);
}

/**
 * Returns logs for given course and appointment.
 * @param courseid - id of course the appointment belongs to
 * @param appointmentid - id of appointment the log belongs to
 * @param typeatt - id of attendance type you wish to filter for (optional)
 * @return array
 */
function tals_get_logs_for_course($courseid, $appointmentid, $typeatt=null) {
  global $DB;

  $result = array();

  if (is_null($typeatt)) {
    $result = $DB->get_records('tals_log', array('courseid' => $courseid, 'fk_appointment_id' => $appointmentid));
  } else {
    $result = $DB->get_records('tals_log', array('courseid' => $courseid, 'fk_appointment_id' => $appointmentid, 'fk_type_attendance_id' => $typeatt));
  }

  return $result;
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

  $logs = $DB->get_records('tals_log', array('fk_appointment_id' => $appointmentid, 'courseid' => $courseid));

  $result = array();

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
  $user = $DB->get_record('user', array('id' => $userid), 'id, username, firstname, lastname, email');

  $profile->id = $user->id;
  $profile->username = $user->username;
  $profile->firstname = $user->firstname;
  $profile->lastname = $user->lastname;
  $profile->email = $user->email;

  $profile->status = tals_get_attendance_count_for_user($user->id, $courseid);

  $logs = $DB->get_records('tals_log', array('userid' => $user->id, 'courseid' => $courseid));

  $profile->countappointments = $DB->count_records('tals_appointment', array('courseid' => $courseid));
  $where = 'courseid = '.$courseid.' AND fk_pin_id IS NOT NULL';
  $profile->countcompulsory = $DB->count_records_select('tals_appointment', $where);

  $now = strtotime(date('d-m-Y H:i', time()));
  $where = 'courseid = '.$courseid.' AND end < '.$now;
  $courseapps = $DB->get_records_select('tals_appointment', $where);
  $userapps = array();

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
    $tmp->end = $entry->end;
    $tmp->duration = round(abs($entry->end - $entry->start) / 60, 2);
    $type = $DB->get_record('tals_type_appointment', array('id' => $entry->fk_type_appointment_id), 'title');
    $tmp->type = $type->title;
    $tmp->groupid = $entry->groupid;

    foreach ($logs as $log) {
      if ($entry->id == $log->fk_appointment_id) {
        $att = $DB->get_record('tals_type_attendance', array('id' => $log->fk_type_attendance_id), 'description');
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

  $result = array();
  $tmp = array();

  $course = $DB->get_record('course', array('id' => $courseid));
  $context = context_course::instance($courseid);
  $users = get_enrolled_users($context, '', 0, 'u.id, u.firstname, u.lastname', null, 0, 0, true);
  $countappointments = $DB->count_records('tals_appointment', array('courseid' => $course->id));
  $where = 'courseid = '.$course->id.' AND fk_pin_id IS NOT NULL';
  $countcompulsory = $DB->count_records_select('tals_appointment', $where);

  // create header with info about the course
  $tmp = array($course->fullname);
  array_push($result, $tmp);

  // current date to know, if its up to date or not
  $tmp = array(date('d.m.Y, H:i', time()));
  array_push($result, $tmp);

  // count of all appointments of this course
  $tmp = array(get_string('label_countappointments', 'tals'), $countappointments);
  array_push($result, $tmp);

  // count how many of them are compulsory
  $tmp = array(get_string('label_compulsory', 'tals'), $countcompulsory);
  array_push($result, $tmp);

  // headline
  $tmp = array(get_string('label_name', 'tals'), get_string('Present_full', 'tals'), get_string('Absent_full', 'tals'), get_string('Excused_full', 'tals'));
  array_push($result, $tmp);

  // everything about the users
  foreach ($users as $user) {
    $fullname = $user->firstname.' '.$user->lastname;
    $status = tals_get_attendance_count_for_user($user->id, $course->id);

    $tmp = array($fullname, $status->present, $status->absent, $status->excused);
    array_push($result, $tmp);
  }

  return $result;
}