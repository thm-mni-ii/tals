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
 * Provides service to delete a given appointment
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
$appid = required_param('appid', PARAM_INT);
$issure = optional_param('issure', false, PARAM_BOOL);

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

$PAGE->set_url('/mod/tals/delete.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

// Check if user has already confirmend deletion.
if ($issure) {
    tals_delete_appointment($appid);

    // After execution redirect to manage-view.
    redirect(new moodle_url('/mod/tals/manage.php', ['id' => $id]));
} else {
    echo $OUTPUT->header();

    global $DB;

    $appointment = tals_get_single_appointment($appid);

    // Content.
    echo '<div id="alertBox">
            <p class="text">' . get_string('label_issure', 'tals') . '</p>
            <p class="text">
                <b>
                    ' . $appointment->title . ' (' . $appointment->type . ')<br>
                    ' . date('d.m.Y', $appointment->start) . '<br>
                    ' . date('H:i', $appointment->start) . ' - ' . date('H:i', $appointment->end) . ' '
        . get_string('label_hour', 'tals') . '<br>
                </b>
            </p>
            <div id="buttonBox">
                <a style="text-decoration: none;" href="'
        . new moodle_url('/mod/tals/delete.php', ['id' => $id, 'appid' => $appid, 'issure' => true])
        . '"><input type="button" id="buttonDelete" name="delete" value="' . get_string('label_trash', 'tals') . '"></a>
                <a style="text-decoration: none;" href="' . new moodle_url('/mod/tals/manage.php', ['id' => $id])
        . '"><input type="submit" id="buttonCancel" name="cancel" value="' . get_string('label_cancel', 'tals') . '"></a>
            </div>
        </div>';

    echo $OUTPUT->footer();
}
