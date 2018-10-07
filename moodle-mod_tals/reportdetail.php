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
 * Provides detailed reports for single appointments.
 *
 * @package     mod_tals
 * @copyright   2017 Technische Hochschule Mittelhessen - University of Applied Sciences - Giessen, Germany
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/locallib.php');
require_once($CFG->libdir . '/accesslib.php');

// Course_module ID, or.
$id = required_param('id', PARAM_INT);
$userid = optional_param('userid', null, PARAM_INT);
$appid = optional_param('appid', null, PARAM_INT);

// ... module instance id.
$t = optional_param('t', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('tals', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('tals', ['id' => $cm->instance], '*', MUST_EXIST);
} else if ($t) {
    $moduleinstance = $DB->get_record('tals', ['id' => $n], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('tals', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', 'tals'));
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

// Check manager capabilities.
$capabilities = [
    'mod/tals:manage',
    'mod/tals:change',
    'mod/tals:viewreports'
];

if (!has_any_capability($capabilities, $modulecontext)) {
    print_error(get_string('nopermission', 'tals'));
}

$PAGE->set_url('/mod/tals/reportdetail.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();


$appointment = $DB->get_record('tals_appointment', ['id' => $appid]);


$list = tals_get_attendance_report_for_appointment($course->id, $appid);
foreach ($list as $entry) {
    $entry->profileurl = new moodle_url('/mod/tals/profile.php', ['id' => $id, 'student' => $entry->userid]);
    if($entry->attendance == PRESENT) {
        $entry->present = true;
        $entry->excused = false;
    } else if ($entry->attendance == EXCUSED) {
        $entry->present = false;
        $entry->excused = true;
    } else {
        $entry->present = false;
        $entry->excused = false;
    }

    if ($entry->acceptance == INTERNAL) {
        $entry->internal = true;
        $entry->vpn = false;
    } else if ($entry->acceptance == VPN) {
        $entry->internal = false;
        $entry->vpn = true;
    } else {
        $entry->internal = false;
        $entry->vpn = false;
    }
    $entry->editurl = new moodle_url('/mod/tals/edit.php', [
        'id' => $id,
        'userid' => $entry->userid,
        'courseid' => $course->id,
        'appid' => $appid
    ]);

}
$context = new stdClass();
$context->addurl = new moodle_url('/mod/tals/add.php', ['id' => $id]);
$context->reporturl =  new moodle_url('/mod/tals/report.php', ['id' => $id]);
$context->manageurl =  new moodle_url('/mod/tals/manage.php', ['id' => $id]);
$context->reportdetailurl = new moodle_url('/mod/tals/reportdetail.php', ['id' => $id, 'appid' => $appid]);
$context->count = count(tals_get_logs_for_course($course->id, $appid, PRESENT));
$context->entries = $list;

echo $OUTPUT->render_from_template('tals/reportdetail', $context);

echo $OUTPUT->footer();