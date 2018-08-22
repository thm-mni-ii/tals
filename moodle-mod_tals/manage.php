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
 * Provides manage-view with list of all appointments of given course.
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

$PAGE->set_url('/mod/tals/manage.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

$data = new stdClass();
$data->add =  new moodle_url('/mod/tals/add.php', ['id' => $id]);
$data->report =  new moodle_url('/mod/tals/report.php', ['id' => $id]);

echo $OUTPUT->render_from_template('tals/manage', $data);
/*

// Header.
echo '
<div class="container">
    <nav class="navbar navbar-expand-md">
    <li class="nav-item list-unstyled" >
        <a>' . get_string('label_header_date', 'tals') . '</a>
    </li>
    <li class="nav-item list-unstyled">
        <a class="nav-link" href="' . new moodle_url('/mod/tals/add.php', ['id' => $id]) . '">' . get_string('label_header_add', 'tals') . '</a>
    </li>
    <li class="nav-item list-unstyled">
        <a class="nav-link" href="' . new moodle_url('/mod/tals/report.php', ['id' => $id]) . '">' . get_string('label_header_report', 'tals') . '</a>
    </li>
    </nav>
</div>';

// Content.
echo '<div id="Termin" class="container">
      <p>
       <h3>' . get_string('label_date', 'tals') . '</h3>
      </p>
      <table id="tabelle" class="table">
      <thead>
      <tr>
        <th>' . get_string('label_id', 'tals') . '</th>
        <th>' . get_string('label_name', 'tals') . '</th>
        <th>' . get_string('label_description', 'tals') . '</th>
        <th>' . get_string('label_start', 'tals') . '</th>
        <th>' . get_string('label_end', 'tals') . '</th>
        <th>' . get_string('label_duration', 'tals') . '</th>
        <th>' . get_string('label_type', 'tals') . '</th>
        <th>' . get_string('label_edit', 'tals') . '</th>
      </tr>
      </thead>
      </table>
</div>';*/



$list = tals_get_all_appointments_of_course($course->id);
$iswhite = false;
$lastgroup = 0;
//
//foreach ($list as $entry) {
//    /*if ($lastgroup != $entry->groupid) {
//        $iswhite = !$iswhite;
//        $lastgroup = $entry->groupid;
//    }*/
//
//    /*if ($iswhite) {
//        echo '<tr bgcolor="#E8E8E8">';
//    } else {
//        echo '<tr>';
//    }*/
//
//    echo '<tr>';
//    echo '<td>' . $entry->id . '</td>
//        <td><a href="' . new moodle_url('/mod/tals/reportdetail.php', ['id' => $id, 'appid' => $entry->id]) . '">'
//        . $entry->title . '</a></td>
//        <td>' . $entry->description . '</td>
//        <td align="center">' . date('d.m.Y, H:i', $entry->start) . '</td>
//        <td align="center">' . date('d.m.Y, H:i', $entry->end) . '</td>
//        <td>' . $entry->duration . ' ' . get_string('label_minute', 'tals') . '</td>
//        <td>' . $entry->type . '</td>
//        <td>';
//
//    if (!is_null($entry->pin)) {
//        if (tals_check_for_enabled_pin($entry->id)) {
//            echo '<b>' . get_string('label_pin', 'tals') . ':</b> ' . $entry->pin
//                . ' (' . get_string('label_until', 'tals') . ' ' . date('H:i', $entry->pinuntil)
//                . ' ' . get_string('label_hour', 'tals') . ') <img src="pix/show.png" alt="'
//                . get_string('label_show', 'tals') . '" height="15" width="15">';
//        } else {
//            echo '<b>' . get_string('label_pin', 'tals') . ':</b> ' . $entry->pin
//                . ' (' . $entry->pindur . ' ' . get_string('label_minute', 'tals') . ') <a href="'
//                . new moodle_url('/mod/tals/enablepin.php', ['id' => $id, 'appid' => $entry->id])
//                . '"><img src="pix/hide.png" alt="' . get_string('label_hide', 'tals')
//                . '" height="15" width="15"></a>';
//        }
//    }
//
//    echo '<br><a href="' . new moodle_url('/mod/tals/change.php', ['id' => $id, 'appid' => $entry->id])
//        . '"><img src="pix/edit.png" alt="' . get_string('label_edit', 'tals')
//        . '" height="15" width="15"></a> <a href="' . new moodle_url('/mod/tals/delete.php', ['id' => $id, 'appid' => $entry->id])
//        . '"><img src="pix/trash.png" alt="' . get_string('label_trash', 'tals')
//        . '" height="15" width="15"></a>';
//
//    echo '</td>
//        </tr>';
//}
//
//echo '</tbody>
//      </table>
//      </div>';

echo $OUTPUT->footer();