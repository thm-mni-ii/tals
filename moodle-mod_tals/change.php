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
 * Provides form to change a given appointment
 *
 * @package     mod_tals
 * @copyright   2017 Technische Hochschule Mittelhessen - University of Applied Sciences - Giessen, Germany
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/locallib.php');
require_once($CFG->libdir . '/accesslib.php');

global $DB;

// Course_module ID, or.
$id = required_param('id', PARAM_INT);
$appid = required_param('appid', PARAM_INT);

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

$PAGE->set_url('/mod/tals/change.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

// JavaScript to disable PIN if not needed.
?>
    <script type="text/javascript">
        function toggleDisabled_pin(_checked) {
            document.getElementById('duration').disabled = _checked ? false : true;
        }
    </script>
<?php

// Header.
echo '<ul id="liste">
    <li class="element"><a href="' . new moodle_url('/mod/tals/manage.php', ['id' => $id]) . '">'
    . get_string('label_header_date', 'tals') . '</a></li>
    <li class="element"><a href="' . new moodle_url('/mod/tals/add.php', ['id' => $id]) . '">'
    . get_string('label_header_add', 'tals') . '</a></li>
    <li class="element"><a href="' . new moodle_url('/mod/tals/report.php', ['id' => $id]) . '">'
    . get_string('label_header_report', 'tals') . '</a></li>
  </ul>';

if (!$DB->record_exists('tals_appointment', ['id' => $appid])) {
    print_error(get_string('noappointment', 'tals'));
}

$appointment = $DB->get_record('tals_appointment', ['id' => $appid]);

// Content.
echo '<div id="TerminHinzu" class="tabcontent">
    <p><h3>' . get_string('label_edit', 'tals') . ' ' . get_string('label_header_date', 'tals') . '</h3></p>
    <form action="' . new moodle_url('/mod/tals/changeappointment.php', ['id' => $id, 'appid' => $appid])
    . '" method="post" id="formular">

      <!-- Overview -->
      <div class="rahmen">
        <b>' . get_string('label_header_date', 'tals') . '</b>
          <table>

            <!-- Row 1-->
            <tr>
              <td class="description_cell">
                <p class="description">' . get_string('label_type', 'tals') . '</p>
              </td>
              <td>
              <select name="ART_type">';

// Get all types of appointment to list them.
$types = $DB->get_records('tals_type_appointment');

foreach ($types as $entry) {
    if ($entry->id == $appointment->fk_type_appointment_id) {
        echo '<option value="' . $entry->id . '" selected>' . $entry->title . '</option>';
    } else {
        echo '<option value="' . $entry->id . '">' . $entry->title . '</option>';
    }
}

echo '</select>
            </td>
            </tr>

            <!-- Row 2-->
            <tr>
              <td class="description_cell">
                <p class="description">' . get_string('label_name', 'tals') . ' *</p>
              </td>
              <td>
                <input type="text" id="terminName" name="ART_name" value="' . $appointment->title . '" required>
              </td>
            </tr>

            <!-- Row 3-->
            <tr>
              <td class="description_cell">
                <p class="description">' . get_string('label_description', 'tals') . '</p>
              </td>
              <td>
                <textarea id="textfeld" name="ART_description" form="formular">' . $appointment->description . '</textarea>
              </td>
            </tr>
          <tr>
          <td class="description_cell">
            <p class="description">' . get_string('label_period', 'tals') . ' *</p>
          </td>
          <td>
            <p class="description">' . get_string('label_at', 'tals') . ' </p>
              <input type="date" id="groupDate" name="GROUP_date" value="' . date('Y-m-d', $appointment->start) . '" required>
            <p class="description"> ' . get_string('label_from', 'tals') . ' </p>
              <input type="time" id="groupTimeBegin" name="GROUP_time_begin" value="'
    . date('H:i', $appointment->start) . '" required>
            <p class="description"> ' . get_string('label_until', 'tals') . ' </p>
              <input type="time" id="groupTimeEnd" name="GROUP_time_end" value="' . date('H:i', $appointment->end) . '" required>
          </td>
        </tr>
        </table>
      </div>';

// PIN-Section.
echo '<div class="rahmen">
        <b>' . get_string('label_pin', 'tals') . '</b>
        <table>
          <!-- Row 1 -->
          <tr>
            <td class="description_cell">
            </td>
            <td>';

// Check if appointment has PIN and print matching HTML.
if (is_null($appointment->fk_pin_id)) {
    $duration = true;
    echo '<input type="checkbox" name="PIN_true" value="true" onchange="toggleDisabled_pin(this.checked)"> '
        . get_string('label_iscompulsory', 'tals') . ' (' . get_string('label_pininfo', 'tals') . ')';
} else {
    $duration = false;
    $pin = $DB->get_record('tals_pin', ['id' => $appointment->fk_pin_id]);
    echo '<input type="checkbox" name="PIN_true" value="true" onchange="toggleDisabled_pin(this.checked)" checked> '
        . get_string('label_iscompulsory', 'tals') . ' (' . get_string('label_pininfo', 'tals') . ')';
}

echo '</td>
  </tr>

  <!-- Row 2 -->
  <tr>
    <td class="description_cell">
      <p class="description">' . get_string('label_duration', 'tals') . '</p>
    </td>
    <td>';

if ($duration) {
    echo '<select id="duration" name="PIN_duration" disabled="true">';
} else {
    echo '<select id="duration" name="PIN_duration">';
}

// Generate list of possible durations for PIN.
$begin = 1;
$stop = 60;
$width = 5;

if ($duration) {
    $selected = 15;
} else {
    $selected = $pin->duration;
}

// Part-list with 1-min-steps.
for ($i = $begin; $i < $width; $i++) {
    if ($i != $selected) {
        echo '<option value="' . $i . '">' . $i . '</option>';
    } else {
        echo '<option value="' . $i . '" selected>' . $i . '</option>';
    }
}

// Part-list with $width-steps.
for ($j = $width; $j <= $stop; $j = $j + $width) {
    if ($j != $selected) {
        echo '<option value="' . $j . '">' . $j . '</option>';
    } else {
        echo '<option value="' . $j . '" selected>' . $j . '</option>';
    }
}

echo '</select>
      <p class="description"> ' . get_string('label_minute', 'tals') . '</p>
      </td>
    </tr>
  </table>
</div>';

// End.
echo '<div>
        <input type="submit" id="setpin" value="' . get_string('label_safe', 'tals') . '" style="border-radius: 0.4em;">
      </div>

      </form>
  <p>* - ' . get_string('label_required', 'tals') . '</p>
  </div>';

echo $OUTPUT->footer();