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
 * Provides service to check and insert an appointment into the database.
 *
 * @package     mod_tals
 * @copyright   2017 Technische Hochschule Mittelhessen - University of Applied Sciences - Giessen, Germany
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/locallib.php');
require_once($CFG->libdir.'/accesslib.php');

// Course_module ID, or
$id = required_param('id', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$type = optional_param('ART_type', 0, PARAM_INT);
$title = optional_param('ART_name', "", PARAM_TEXT);
$description = optional_param('ART_description', "", PARAM_TEXT);
$pin = optional_param('PIN_true', false, PARAM_BOOL);
$pindur = optional_param('PIN_duration', 0, PARAM_INT);
$group = array(
            array(
                "date" => optional_param('GROUP_date_0', null, PARAM_TEXT),
                "start" => optional_param('GROUP_time_begin_0', null, PARAM_TEXT),
                "end" => optional_param('GROUP_time_end_0', null, PARAM_TEXT)
            ),
            array(
                "date" => optional_param('GROUP_date_1', null, PARAM_TEXT),
                "start" => optional_param('GROUP_time_begin_1', null, PARAM_TEXT),
                "end" => optional_param('GROUP_time_end_1', null, PARAM_TEXT)
            ),
            array(
                "date" => optional_param('GROUP_date_2', null, PARAM_TEXT),
                "start" => optional_param('GROUP_time_begin_2', null, PARAM_TEXT),
                "end" => optional_param('GROUP_time_end_2', null, PARAM_TEXT)
            ),
            array(
                "date" => optional_param('GROUP_date_3', null, PARAM_TEXT),
                "start" => optional_param('GROUP_time_begin_3', null, PARAM_TEXT),
                "end" => optional_param('GROUP_time_end_3', null, PARAM_TEXT)
            ),
            array(
                "date" => optional_param('GROUP_date_4', null, PARAM_TEXT),
                "start" => optional_param('GROUP_time_begin_4', null, PARAM_TEXT),
                "end" => optional_param('GROUP_time_end_4', null, PARAM_TEXT)
            ),
            array(
                "date" => optional_param('GROUP_date_5', null, PARAM_TEXT),
                "start" => optional_param('GROUP_time_begin_5', null, PARAM_TEXT),
                "end" => optional_param('GROUP_time_end_5', null, PARAM_TEXT)
            ),
            array(
                "date" => optional_param('GROUP_date_6', null, PARAM_TEXT),
                "start" => optional_param('GROUP_time_begin_6', null, PARAM_TEXT),
                "end" => optional_param('GROUP_time_end_6', null, PARAM_TEXT)
            ),
            array(
                "date" => optional_param('GROUP_date_7', null, PARAM_TEXT),
                "start" => optional_param('GROUP_time_begin_7', null, PARAM_TEXT),
                "end" => optional_param('GROUP_time_end_7', null, PARAM_TEXT)
            ),
            array(
                "date" => optional_param('GROUP_date_8', null, PARAM_TEXT),
                "start" => optional_param('GROUP_time_begin_8', null, PARAM_TEXT),
                "end" => optional_param('GROUP_time_end_8', null, PARAM_TEXT)
            ),
            array(
                "date" => optional_param('GROUP_date_9', null, PARAM_TEXT),
                "start" => optional_param('GROUP_time_begin_9', null, PARAM_TEXT),
                "end" => optional_param('GROUP_time_end_9', null, PARAM_TEXT)
            )
        );
$weekcount = optional_param('REPEAT_week', 1, PARAM_INT);

global $DB;

// ... module instance id.
$t  = optional_param('t', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('tals', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('tals', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($t) {
    $moduleinstance = $DB->get_record('tals', array('id' => $n), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('tals', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', 'tals'));
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

// check manager capabilities
$capabilities = array(
    'mod/tals:manage',
    'mod/tals:change',
    'mod/tals:viewreports'
);

if (!has_any_capability($capabilities, $modulecontext)) {
  print_error(get_string('nopermission', 'tals'));
}

$PAGE->set_url('/mod/tals/addappointment.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

// Step along the list of appointments as long as $weekcount defines
for ($i = 0, $c = 'a'; $i < $weekcount; $i++, $c++) {
    $gid = $DB->get_record_sql('SELECT MAX(groupid) AS "max" FROM {tals_appointment}');
    $groupid = $gid->max + 1;
    $k = $i + 1;

    // Check and insert appointment into database
    foreach ($group as $entry) {
        if (empty($entry['date']) || empty($entry['start']) || empty($entry['end'])) {
            continue;
        }

        $thisstart = strtotime('+'.$i.' week', strtotime($entry['date'].' '.$entry['start']));
        $thisend = strtotime('+'.$i.' week', strtotime($entry['date'].' '.$entry['end']));
        $thistitle = $title;

        if ($weekcount > 1) {
            $thistitle .= ' ('.$k.$c.')';
        }

        tals_update_appointment(null, $thistitle, $thisstart, $thisend, $description, $courseid, $groupid, $type, $pin, $pindur);
    }
}

// After execution redirect to manage-view
redirect(new moodle_url('/mod/tals/manage.php', array('id' => $id)));