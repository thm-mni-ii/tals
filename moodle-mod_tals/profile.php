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
 * Provides profile view for students with information about compulsory appointments.
 *
 * @package     mod_tals
 * @copyright   2017 Technische Hochschule Mittelhessen - University of Applied Sciences - Giessen, Germany
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/locallib.php');
require_once($CFG->libdir . '/accesslib.php');

global $USER;

// Course_module ID, or.
$id = required_param('id', PARAM_INT);
$student = optional_param('student', null, PARAM_INT);

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
require_capability('mod/tals:view', $modulecontext);
$sudo = false;
$userid = $USER->id;

// Check manager capabilities.
$capabilities = [
    'mod/tals:manage',
    'mod/tals:change',
    'mod/tals:viewreports'
];

if (has_any_capability($capabilities, $modulecontext)) {
    $sudo = true;
}

if (!is_null($student)) {
    $userid = $student;
}

$PAGE->set_url('/mod/tals/profile.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$user = tals_get_user_profile_for_course($userid, $course->id);

$context = new stdClass();

if ($sudo) {
    // Sudo-Header.
    echo '<ul id="liste">
      <li class="element"><a href="' . new moodle_url('/mod/tals/manage.php', ['id' => $id]) . '">'
        . get_string('label_header_date', 'tals') . '</a></li>
      <li class="element"><a href="' . new moodle_url('/mod/tals/add.php', ['id' => $id]) . '">'
        . get_string('label_header_add', 'tals') . '</a></li>
      <li class="element"><a href="' . new moodle_url('/mod/tals/report.php', ['id' => $id]) . '">'
        . get_string('label_header_report', 'tals') . '</a></li>
    </ul>';
}

$user = tals_get_user_profile_for_course($userid, $course->id);

$context->firstname = $user->firstname;
$context->lastname = $user->lastname;
$context->email = $user->email;
$context->countappointments =  $user->countappointments;
$context->countcompulsory = $user->countcompulsory;
$context->present = $user->status->present;
$context->absent = $user->status->absent;
$context->excused = $user->status->excused;

$context->profileurl = new moodle_url('/user/profile.php', ['id' => $user->id]);
$context->profileoverviewurl = new moodle_url('/mod/tals/profileoverview.php', ['id' => $id]);

$context->appointments = array();

foreach ($user->userapps as $entry) {
    $appointment = new stdClass();
    $appointment->title = $entry->title;
    $appointment->description = $entry->description;
    $appointment->start = date('d.m.Y, H:i', $entry->start);
    $appointment->ending = date('d.m.Y, H:i', $entry->ending);
    $appointment->duration = $entry->title;
    $appointment->type = $entry->type;
    $appointment->attendance = $entry->attendance;

    $context->appointments[] = $appointment;
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('tals/profile', $context);
echo $OUTPUT->footer();