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

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/locallib.php');
require_once($CFG->libdir.'/accesslib.php');

// Course_module ID, or
$id = required_param('id', PARAM_INT);
$userid  = optional_param('userid', null, PARAM_INT);
$appid = optional_param('appid', null, PARAM_INT);

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

$PAGE->set_url('/mod/tals/reportdetail.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

echo '<style type="text/css">
      
      #tabelle {
          font-size: 13px;
          border-collapse: collapse;
          width: 100%;
      }

      #tabelle td {
          border: 1px solid #ddd;
          padding: 8px;
      }

      #tabelle th {
          border: 1px solid #ddd;
          padding: 8px;
          padding-top: 12px;
          padding-bottom: 12px;
          text-align: left;
          background-color: #333;
          color: white;
      }

      tr:hover {
          background-color: #ccc;
      }

      .rahmen {
          border: 1px solid #ddd;
          padding: 4px;
          margin: 5px;
          padding-top: 10px;
      }

      .description {
          display:inline;
      }

      .description_cell {
          width:150px;
      }
      
      #liste {
          list-style-type: none;
          margin: 0;
          padding: 0;
          overflow: hidden;
          background-color: #333;
      }

      .element {
          float: left;
      }

      .element a {
          display: inline-block;
          color: white;
          text-align: center;
          padding: 14px 16px;
          text-decoration: none;
      }

      .element a:hover {
          background-color: #111;
      }

      #li_active {
          background-color: #80ba24;
      }

      #li_active a:hover {
          background-color: #80ba24;
      }

      .signlight {
          text-align: center;
          width: 6%;
      }

      .light {
          width: 8px;
          height: 8px;
      }

  </style>';

// Header
echo '<ul id="liste">
    <li class="element"><a href="'.new moodle_url('/mod/tals/manage.php', array('id' => $id)).'">'.get_string('label_header_date', 'tals').'</a></li>
    <li class="element"><a href="'.new moodle_url('/mod/tals/add.php', array('id' => $id)).'">'.get_string('label_header_add', 'tals').'</a></li>
    <li class="element"><a href="'.new moodle_url('/mod/tals/report.php', array('id' => $id)).'">'.get_string('label_header_report', 'tals').'</a></li>
  </ul>';

$appointment = $DB->get_record('tals_appointment', array('id' => $appid));

echo '<div id="Bericht" class="tabcontent">
      <p><h3>'.get_string('label_reportdetail', 'tals').'</h3></p>
      
      <div style="overflow: hidden;">
          <p style="display:inline; float: left; font-size:2em;"><i>'.$appointment->title.'</i></p>
          <p style="display:inline; float: right; font-size:1.25em;">'.get_string('label_count', 'tals').': '.count(tals_get_logs_for_course($course->id, $appointment->id, PRESENT)).' <a href="'.new moodle_url('/mod/tals/reportdetail.php', array('id' => $id, 'appid' => $appointment->id)).'"><img src="pix/reload.png" alt="'.get_string('label_reload', 'tals').'" height="15" width="15"></a></p>
      </div>
      
      <table id="tabelle">
          <tbody>
          <tr>
              <th>'.get_string('label_name', 'tals').'</th>
              <th>'.get_string('label_email', 'tals').'</th>
              <th>'.get_string('label_status', 'tals').'</th>
              <th>'.get_string('label_net', 'tals').'</th>
              <th>'.get_string('label_comment', 'tals').'</th>
              <th>'.get_string('label_edit', 'tals').'</th>
          </tr>';

$list = tals_get_attendance_report_for_appointment($course->id, $appid);
$iswhite = true;

foreach ($list as $entry) {
  if ($iswhite) {
    echo '<tr bgcolor="#E8E8E8">';
    $iswhite = !$iswhite;
  } else {
    echo '<tr>';
    $iswhite = !$iswhite;
  }

  echo '<td><a href="'.new moodle_url('/mod/tals/profile.php', array('id' => $id, 'student' => $entry->userid)).'">'.$entry->firstname.' '.$entry->lastname.'</a></td>
        <td>'.$entry->email.'</td>';
  
  echo '<td class="signlight">';
  
  if ($entry->attendance == PRESENT) {
    echo '<img class="light" src="pix/gruen.png" alt="'.get_string('label_green', 'tals').'">';
  } else if ($entry->attendance == EXCUSED) {
    echo '<img class="light" src="pix/gelb.png" alt="'.get_string('label_yellow', 'tals').'">';
  } else if ($entry->attendance == ABSENT) {
    echo '<img class="light" src="pix/rot.png" alt="'.get_string('label_red', 'tals').'">';
  }

  echo '</td>
        <td class="signlight">';

  if ($entry->acceptance == INTERNAL) {
    echo '<img src="pix/mnethost-internal.png" alt="'.get_string('label_green', 'tals').'" height="15" width="15">';
  } else if ($entry->acceptance == VPN) {
    echo '<img src="pix/mnethost-vpn.png" alt="'.get_string('label_yellow', 'tals').'" height="15" width="15">';
  } else {
    echo '<img src="pix/mnethost-external.png" alt="'.get_string('label_red', 'tals').'" height="15" width="15">';
  }

  echo '</td>
        <td>'.$entry->comment.'</td>
        <td><a href="'.new moodle_url('/mod/tals/edit.php', array('id' => $id, 'userid' => $entry->userid, 'appid' => $appid, 'courseid' => $course->id)).'"><img src="pix/edit.png" alt="'.get_string('label_edit', 'tals').'" height="12" width="12"></a></td>
        </tr>';
}

echo '</tbody>
    </table>
    <p>'.get_string('label_legend', 'tals').':<br> 
      <img class="light" src="pix/gruen.png" alt="green"> '.get_string('Present_full', 'tals').'<br> 
      <img class="light" src="pix/gelb.png" alt="yellow"> '.get_string('Excused_full', 'tals').'<br> 
      <img class="light" src="pix/rot.png" alt="red"> '.get_string('Absent_full', 'tals').'<br>
      <img src="pix/mnethost-internal.png" alt="yellow" height="15" width="15"> '.get_string('label_net_green', 'tals').'<br> 
      <img src="pix/mnethost-vpn.png" alt="yellow" height="15" width="15"> '.get_string('label_net_blue', 'tals').'<br> 
      <img src="pix/mnethost-external.png" alt="yellow" height="15" width="15"> '.get_string('label_net_grey', 'tals').'</p>
    </div>';

echo $OUTPUT->footer();