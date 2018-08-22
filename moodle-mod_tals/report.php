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
 * Provides reports for past appointments.
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

$PAGE->set_url('/mod/tals/report.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();


// Header.
echo '<ul id="liste">
  <li class="element"><a href="' . new moodle_url('/mod/tals/manage.php', ['id' => $id]) . '">'
    . get_string('label_header_date', 'tals') . '</a></li>
  <li class="element"><a href="' . new moodle_url('/mod/tals/add.php', ['id' => $id]) . '">'
    . get_string('label_header_add', 'tals') . '</a></li>
  <li class="element" id="li_active"><a>' . get_string('label_header_report', 'tals') . '</a></li>
</ul>';

// Content.
echo '<div id="Bericht" class="tabcontent">

    <div style="overflow: hidden;">
          <p style="display:inline; float: left; font-size:2em;"><i>' . get_string('label_report', 'tals') . '</i></p>
          <p style="display:inline; float: right; font-size:1.25em;"><a href="'
    . new moodle_url('/mod/tals/reportdownload.php', ['id' => $id])
    . '" target=_blank><img src="pix/download_all.png" alt="' . get_string('label_download', 'tals')
    . '" height="20" width="20"></a></p>
    </div>

    <table id="tabelle">
    <tbody>
    <tr>
      <th>' . get_string('label_id', 'tals') . '</th>
      <th>' . get_string('label_name', 'tals') . '</th>
      <th>' . get_string('label_description', 'tals') . '</th>
      <th>' . get_string('label_start', 'tals') . '</th>
      <th>' . get_string('label_end', 'tals') . '</th>
      <th>' . get_string('label_duration', 'tals') . '</th>
      <th>' . get_string('label_type', 'tals') . '</th>
      <th>' . get_string('label_count', 'tals') . '</th>
    </tr>';

// 2018-01-01 00:00:00, because we needed a start and there can't be appointments before this point.
$start = 1514764800;
$end = strtotime(date('d-m-Y H:i', time()));
$list = tals_get_appointments($course->id, $start, $end);
$iswhite = false;
$lastgroup = 0;

foreach ($list as $entry) {
    if ($lastgroup != $entry->groupid) {
        $iswhite = !$iswhite;
        $lastgroup = $entry->groupid;
    }

    $logs = tals_get_logs_for_course($course->id, $entry->id, PRESENT);

    if ($iswhite) {
        echo '<tr bgcolor="#E8E8E8">';
    } else {
        echo '<tr>';
    }

    echo '<td>' . $entry->id . '</td>
        <td><a href="' . new moodle_url('/mod/tals/reportdetail.php', ['id' => $id, 'appid' => $entry->id]) . '">'
        . $entry->title . '</a></td>
        <td>' . $entry->description . '</td>
        <td align="center">' . date('d.m.Y, H:i', $entry->start) . '</td>
        <td align="center">' . date('d.m.Y, H:i', $entry->ending) . '</td>
        <td>' . $entry->duration . ' ' . get_string('label_minute', 'tals') . '</td>
        <td>' . $entry->type . '</td>
        <td align="center">' . count($logs) . '</td>
      </tr>';
}

echo '</tbody>
    </table>
    </div>';

echo $OUTPUT->footer();