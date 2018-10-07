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
 * Provides profile view for students with information about the courses appointments.
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

echo $OUTPUT->header();

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
$appointments = tals_get_all_appointments_of_course($course->id);

$status = $user->status;

echo '<div class="rahmen">
      <h2><a href="' . new moodle_url('/user/profile.php', ['id' => $user->id]) . '">' . $user->firstname
    . ' ' . $user->lastname . '</a></h2>
      <p style="font-size: 0.8em;"><a href="mailto:' . $user->email . '">' . $user->email . '</a></p>
      <table>
          <tr>
              <td style="width: 100px;"><p>' . get_string('label_countappointments', 'tals') . ': </p></td>
              <td><p>' . $user->countappointments . ' (' . $user->countcompulsory . ' '
    . get_string('label_arecompulsory', 'tals') . ')</p></td>
          </tr>
          <tr>
              <td style="width: 100px;"><p>' . get_string('label_attendance', 'tals') . ': </p></td>
              <td><p>' . $status->present . '</p></td>
          </tr>
          <tr>
              <td style="width: 100px;"><p>' . get_string('label_daysabsent', 'tals') . ': </p></td>
              <td><p>' . $status->absent . '</p></td>
          </tr>
          <tr>
              <td style="width: 100px;"><p>' . get_string('label_excused', 'tals') . ': </p></td>
              <td><p>' . $status->excused . '</p></td>
          </tr>
      </table>
  </div>';

if (!$sudo) {
    // User-Header.
    echo '<ul id="liste">
        <li class="element"><a href="' . new moodle_url('/mod/tals/profile.php', ['id' => $id]) . '">'
        . get_string('label_myattendance', 'tals') . '</a></li>
        <li class="element" id="li_active"><a>' . get_string('label_courseoverview', 'tals') . '</a></li>
      </ul>';
}

echo '<div id="Bericht" class="tabcontent">
      <table id="tabelle">
          <tbody>
          <tr>
              <tr>
              <th>' . get_string('label_name', 'tals') . '</th>
              <th>' . get_string('label_description', 'tals') . '</th>
              <th>' . get_string('label_start', 'tals') . '</th>
              <th>' . get_string('label_end', 'tals') . '</th>
              <th>' . get_string('label_duration', 'tals') . '</th>
              <th>' . get_string('label_type', 'tals') . '</th>
              <th>' . get_string('label_compulsory', 'tals') . '</th>
          </tr>';

$iswhite = false;
$lastgroup = 0;

foreach ($appointments as $entry) {
    if ($lastgroup != $entry->groupid) {
        $iswhite = !$iswhite;
        $lastgroup = $entry->groupid;
    }

    if ($iswhite) {
        echo '<tr bgcolor="#E8E8E8">';
    } else {
        echo '<tr>';
    }

    echo '<td>' . $entry->title . '</td>
        <td>' . $entry->description . '</td>
        <td>' . date('d.m.Y, H:i', $entry->start) . '</td>
        <td>' . date('d.m.Y, H:i', $entry->ending) . '</td>
        <td>' . $entry->duration . ' ' . get_string('label_minute', 'tals') . '</td>
        <td>' . $entry->type . '</td>';

    if (!is_null($entry->pin)) {
        echo '<td class="signlight"><img src="pix/compulsory.png" alt="'
            . get_string('label_iscompulsory', 'tals') . '" height="20" width="30"></td>';
    } else {
        echo '<td></td>';
    }

    echo '</tr>';
}

echo '</tbody>
    </table>
    </div>
    <p><br><img src="pix/compulsory.png" alt="' . get_string('label_iscompulsory', 'tals')
    . '" height="20" width="30"> ' . get_string('label_iscompulsory', 'tals') . '</p>';

echo $OUTPUT->footer();