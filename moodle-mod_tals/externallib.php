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
 * Externallib.php file for tals plugin.
 * Provides functionality used by remote applications (Android, iOS)
 *
 * @package     mod_tals
 * @copyright   2017 Technische Hochschule Mittelhessen - University of Applied Sciences - Giessen, Germany
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once($CFG->libdir.'/enrollib.php');
require_once(dirname(__FILE__).'/locallib.php');

/**
 * Class mod_wstals_external
 * @copyright   2017 Technische Hochschule Mittelhessen - University of Applied Sciences - Giessen, Germany
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_wstals_external extends external_api {

  /**
   * Returns description of get_todays_appointments parameters.
   * @return external_function_parameters
   */
  public static function get_todays_appointments_parameters() {
    return new external_function_parameters(
                array(
                  'userid' => new external_value(PARAM_INT, 'id of user', VALUE_REQUIRED),
                  'courseid' => new external_value(PARAM_INT, 'id of course', VALUE_DEFAULT, null)
                )
              );
  }

  /**
   * Returns a list of all appointments of a user of current day (0:00h 'til 23:59h).
   * @param userid - id of user
   * @param courseid - id of course
   * @return array - see get_todays_apoointsments_return()
   */
  public static function get_todays_appointments($userid, $courseid) {
    global $DB;

    $params = self::validate_parameters(self::get_todays_appointments_parameters(), array('userid' => $userid, 'courseid' => $courseid));

    $result = array();
    $start = strtotime(date('d-m-Y', time()));
    $end = strtotime('+1 days', $start);
    
    $userid = $params['userid'];
    $courseid = $params['courseid'];

    $courses = tals_get_courses($userid, $courseid);

    // build well-formed array of today's appointments
    foreach ($courses as $course) {
      $tmpAppointment = array();
      $tmpAppointment = tals_get_appointments($course->id, $start, $end);

      foreach ($tmpAppointment as $appointment) {
        $tmp = new stdClass;

        $tmp->id = $appointment->id;
        $tmp->title = $appointment->title;
        $tmp->start = date('H:i', $appointment->start);
        $tmp->end = date('H:i', $appointment->end);
        $tmp->description = $appointment->description;
        $tmp->courseid = $appointment->courseid;
        $tmp->type = $appointment->type;
        $tmp->pin = tals_check_for_enabled_pin($appointment->id);

        array_push($result, $tmp);
      }
    }

    return $result;
  }

  /**
   * Returns description of get_todays_appointments return values.
   * @return external_multiple_structure
   */
  public static function get_todays_appointments_returns() {
    return new external_multiple_structure(
                new external_single_structure(
                  array(
                    'id' => new external_value(PARAM_INT, 'id of appointment'),
                    'title' => new external_value(PARAM_TEXT, 'title of appointment'),
                    'start' => new external_value(PARAM_TEXT, 'starttime of appointment (UNIX-Timestamp)'),
                    'end' => new external_value(PARAM_TEXT, 'endtime of appointment (UNIX-Timestamp)'),
                    'description' => new external_value(PARAM_TEXT, 'description of appointment, if existing'),
                    'courseid' => new external_value(PARAM_INT, 'id of associated course'),
                    'type' => new external_value(PARAM_TEXT, 'appointment type'),
                    'pin' => new external_value(PARAM_BOOL, 'true if pin enabled, otherwise false')
                  )
                )
              );
  }

  /**
   * Returns description of insert_attendance parameters.
   * @return external_function_parameters
   */
  public static function insert_attendance_parameters() {
    return new external_function_parameters(
                array(
                  'userid' => new external_value(PARAM_INT, 'id of user', VALUE_REQUIRED),
                  'appointmentid' => new external_value(PARAM_INT, 'id of appointment', VALUE_REQUIRED),
                  'pinum' => new external_value(PARAM_INT, 'pin to prove attendance', VALUE_REQUIRED)
                )
              );
  }

  /**
   * Inserts attendance of user, if pin is correct.
   * @param userid - id of user
   * @param appointmentid - id of appointment
   * @param pinum - PIN provided by user
   * @return string - see insert_attendance_return
   */
  public static function insert_attendance($userid, $appointmentid, $pinum) {
    global $DB;

    $params = self::validate_parameters(self::insert_attendance_parameters(), array('userid' => $userid, 'appointmentid' => $appointmentid, 'pinum' => $pinum));

    $userid = $params['userid'];
    
    /*This approach is used, because a simple $DB->get_record('tals_type_attendance', array('description' => get_string('Present_full', 'tals')), 'id')
    throws error.
    The given hint to fix this is using sql_compare_text(), but this function is not documented. Therefore it can't be used (I tried, really).*/
    $typelist = $DB->get_records('tals_type_attendance');

    foreach ($typelist as $entry) {
      if (strcmp($entry->description, get_string('Present_full', 'tals')) == 0) {
        $typeattendance = $entry;
        break;
      }
    }

    $acceptance = tals_get_type_net();
    $appointmentid = $params['appointmentid'];
    $pinum = $params['pinum'];

    $result = tals_update_attendance($userid, "", $typeattendance->id, $acceptance, $appointmentid, $pinum);
    $response = false;
    $message = get_string('error', 'tals');

    if ($result > SUCCESS) {
      $response = true;
      $message = get_string('success', 'tals');
    } else if ($result == ERROR_NO_ATT_TYPE) {
      $respone = false;
      $message = get_string('typenoattendance', 'tals');
    } else if ($result == ERROR_NO_APPOINTMENT) {
      $respone = false;
      $message = get_string('noappointment', 'tals');
    } else if ($result == ERROR_PIN_WRONG) {
      $response = false;
      $message = get_string('pinwrong', 'tals');
    } else if ($result = ERROR_PIN_DISABLED) {
      $response = false;
      $message = get_string('pinnotenabled', 'tals');
    } else if ($result = FAIL) {
      $respone = false;
      $message = get_string('fail', 'tals');
    } else {
      $response = false;
      $message = get_string('error', 'tals');
    }

    return array('response' => $response, 'message' => $message);
  }

  /**
   * Returns description of insert_attendance return values.
   * @return external_single_structure
   */
  public static function insert_attendance_returns() {
    return new external_single_structure(
                array(
                  'response' => new external_value(PARAM_BOOL, 'response of insertion'),
                  'message' => new external_value(PARAM_TEXT, 'response of insertion')
                )
              );
  }

  /**
   * Returns description of check_for_enabled_pin parameters.
   * @return external_function_parameters
   */
  public static function check_for_enabled_pin_parameters() {
    return new external_function_parameters(
                    array(
                      'userid' => new external_value(PARAM_INT, 'id of user', VALUE_REQUIRED),
                      'appointmentid' => new external_value(PARAM_INT, 'id of appointment', VALUE_REQUIRED),
                    )
              );
  }

  /**
   * Check if Appointment has enabled PIN.
   * @param userid - id of user
   * @param appointmentid - id of appointment
   * @return see check_for_enabled_pin_return
   */
  public static function check_for_enabled_pin($userid, $appointmentid) {
    global $DB;

    $params = self::validate_parameters(self::check_for_enabled_pin_parameters(), array('userid' => $userid, 'appointmentid' => $appointmentid));

    $appointmentid = $params['appointmentid'];
    $userid = $params['userid'];

    $result = new stdClass;
    $result->pin = tals_check_for_enabled_pin($appointmentid);

    $appointment = $DB->get_record('tals_appointment', array('id' => $appointmentid));

    $status = tals_get_attendance_count_for_user($userid, $appointment->courseid);
    $result->daysabsent = $status->absent;

    $result->alreadyattending = tals_is_user_already_attending($appointment->id, $userid);

    return array('pin enabled' => $result->pin, 'days absent' => $result->daysabsent, 'already attending' => $result->alreadyattending);
  }

  /**
   * Returns description of check_for_enabled_pin return values.
   * @return external_single_structure
   */
  public static function check_for_enabled_pin_returns() {
    return new external_single_structure(
                array(
                  'pin enabled' => new external_value(PARAM_BOOL, 'true if pin enabled, otherwise false'),
                  'days absent' => new external_value(PARAM_INT, 'count of days absent'),
                  'already attending' => new external_value(PARAM_BOOL, 'true if user is already logged, otherwise false')
                )
              );
  }

  /**
   * @deprecated This function can still be used, but is no longer maintained (2018-01-15).
   * Returns description of get_days_absent parameters.
   * @return external_function_parameters
   */
  public static function get_days_absent_parameters() {
    return new external_function_parameters(
                array(
                  'userid' => new external_value(PARAM_INT, 'id of user', VALUE_REQUIRED),
                  'courseid' => new external_value(PARAM_INT, 'id of course', VALUE_REQUIRED)
                )
              );
  }

  /**
   * @deprecated This function can still be used, but is no longer maintained (2018-01-15).
   * Returns count of appointments a user missed.
   * @param userid - id of user
   * @param courseid - id of course
   * @return see get_days_absent_return
   */
  public static function get_days_absent($userid, $courseid) {
    $params = self::validate_parameters(self::get_days_absent_parameters(), array('userid' => $userid, 'courseid' => $courseid));

    $userid = $params['userid'];
    $courseid = $params['courseid'];

    $status = tals_get_attendance_count_for_user($userid, $courseid);

    return array('days absent' => $status->absent);
  }

  /**
   * @deprecated This function can still be used, but is no longer maintained (2018-01-15).
   * Returns description of get_days_absent return values.
   * @return external_single_structure
   */
  public static function get_days_absent_returns() {
    return new external_single_structure(
                array(
                  'days absent' => new external_value(PARAM_INT, 'count of days absent')
                )
              );
  }

  /**
   * Returns description of get_courses parameters.
   * @return external_function_parameters
   */
  public static function get_courses_parameters() {
    return new external_function_parameters(
                array(
                  'userid' => new external_value(PARAM_INT, 'id of user', VALUE_REQUIRED)
                )
              );
  }

  /**
   * Returns list of courses a given user is enrolled in.
   * @param userid - id of user
   * @return see get_courses_returns
   */
  public static function get_courses($userid) {
    $params = self::validate_parameters(self::get_courses_parameters(), array('userid' => $userid));

    $userid = $params['userid'];

    return tals_get_courses($userid);
  }

  /**
   * Returns description of get_courses return values.
   * @return external_single_structure
   */
  public static function get_courses_returns() {
    return new external_multiple_structure(
                new external_single_structure(
                  array(
                    'id' => new external_value(PARAM_INT, 'id of course'),
                    'shortname' => new external_value(PARAM_TEXT, 'shortname of course'),
                    'fullname' => new external_value(PARAM_TEXT, 'fullname of course'),
                    'startdate' => new external_value(PARAM_INT, 'startdate of course')
                  )
                )
              );
  }
}