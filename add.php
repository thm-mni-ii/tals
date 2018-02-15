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
 * Enables management of tals-appointments.
 *
 * @package     mod_tals
 * @copyright   2017 Technische Hochschule Mittelhessen - University of Applied Sciences - Giessen, Germany
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/locallib.php');
require_once($CFG->libdir.'/accesslib.php');

global $DB;

// Course_module ID, or
$id = required_param('id', PARAM_INT);

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

$PAGE->set_url('/mod/tals/add.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

// CSS
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

    .rahmen {
        border: 1px solid #ddd;
        padding: 4px;
        margin-bottom: 5px;
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

    #textfeld {
      width: 250px;
      height: 100px;
    }

    #pin {
      width: 40px;
    }

  </style>';

// JavaScript to disable PIN if not needed
?>
<script type="text/javascript">
    function toggleDisabled_pin(_checked) {
        document.getElementById('duration').disabled = _checked ? false : true;
    }

    var count = 1;
    function toggle_visibility(id) {

      var e = document.getElementById(id+count);
        
      e.style.display = 'block';
      count = count+1;
    }
</script>
<?php

// Header
echo '<ul id="liste">
    <li class="element"><a href="'.new moodle_url('/mod/tals/manage.php', array('id' => $id)).'">'.get_string('label_header_date', 'tals').'</a></li>
    <li class="element" id="li_active"><a>'.get_string('label_header_add', 'tals').'</a></li>
    <li class="element"><a href="'.new moodle_url('/mod/tals/report.php', array('id' => $id)).'">'.get_string('label_header_report', 'tals').'</a></li>
  </ul>';

// Content
echo '<div id="TerminHinzu" class="tabcontent">
    <p><h3>'.get_string('label_header_add', 'tals').'</h3></p>
    <form action="'.new moodle_url('/mod/tals/addappointment.php', array('id' => $id, 'courseid' => $course->id)).'" method="post" id="formular">
      
      <!-- UEBERSICHT -->
      <div class="rahmen">
        <b>'.get_string('label_newdate', 'tals').'</b>
          <table>
            
            <!--ZEILE 1-->
            <tr>
              <td class="description_cell">
                <p class="description">'.get_string('label_type', 'tals').'</p>  
              </td>
              <td>
              <select name="ART_type">';

$types = $DB->get_records('tals_type_appointment');

foreach ($types as $entry) {
  echo '<option value="'.$entry->id.'">'.$entry->title.'</option>';
}
               
echo '</select>
            </td>
            </tr>

            <!--ZEILE 2-->
            <tr>
              <td class="description_cell">
                <p class="description">'.get_string('label_name', 'tals').' *</p>
              </td>
              <td>
                <input type="text" id="terminName" name="ART_name" required>
              </td>
            </tr>
            
            <!--ZEILE 3-->
            <tr>
              <td class="description_cell">
                <p class="description">'.get_string('label_description', 'tals').'</p>
              </td>
              <td>
                <textarea id="textfeld" name="ART_description" form="formular"></textarea>
              </td>
            </tr>
          </table>
        </div>

      <!-- GRUPPIERTE TERMINE -->
      <div class="rahmen">
        <b>'.get_string('label_occurrence', 'tals').'</b>
        <table>
          <!-- ZEILE 1 -->
          <tr>
            <td class="description_cell"> 
              <p class="description">'.get_string('label_header_date', 'tals').' *</p>
            </td>
            <td>
              <p class="description">'.get_string('label_at', 'tals').' </p>
                <input type="date" id="groupDate0" name="GROUP_date_0" required>
              <p class="description"> '.get_string('label_from', 'tals').' </p>
                <input type="time" id="groupTimeBegin0" name="GROUP_time_begin_0" required>
              <p class="description"> '.get_string('label_until', 'tals').' </p>
                <input type="time" id="groupTimeEnd0" name="GROUP_time_end_0" required>
            </td>
            <td>
              &nbsp;<img src="pix/add.png" height="15" size="15" alt="'.get_string('label_more', 'tals').'" style="cursor:pointer;" onclick="toggle_visibility(\'hiddenField\');">
            </td>
          </tr>
        </table>';

for ($i = 1; $i < 10; $i++) { // Don't change this count unless you know what you do.
    $j = $i + 1;
echo '<div id="hiddenField'.$i.'" style="display: none;">
        <p class="description">'.get_string('label_header_date', 'tals').' '.$j.' </p>
        <p class="description" style="padding-left: 4.7em;"> '.get_string('label_at', 'tals').' </p>
          <input type="date" id="groupDate'.$i.'" name="GROUP_date_'.$i.'">
        <p class="description"> '.get_string('label_from', 'tals').' </p>
          <input type="time" id="groupTimeBegin'.$i.'" name="GROUP_time_begin_'.$i.'">
        <p class="description"> '.get_string('label_until', 'tals').' </p>
          <input type="time" id="groupTimeEnd'.$i.'" name="GROUP_time_end_'.$i.'">
      </div>';
}

echo '<table>
         <!-- ZEILE (11) 3 -->
        <tr>
        <td class="description_cell">
          <p class="description">'.get_string('label_repeat', 'tals').'</p>
        </td>
        <td>
        <select id="repeatWeek" name="REPEAT_week">';

for ($i = 1; $i <= 20; $i++) { 
  echo '<option value="'.$i.'">'.$i.'</option>';
}

echo '</select> 
      <p class="description">'.get_string('label_weeks', 'tals').'</p>
        </td>
        </tr>

      </table>

    </div>';

// PIN-Section
echo '<div class="rahmen">
        <b>'.get_string('label_pin', 'tals').'</b>
        <table>
          <!-- ZEILE 1 -->
          <tr>
            <td class="description_cell"> 
            </td>
            <td>
              <input type="checkbox" name="PIN_true" value="true" onchange="toggleDisabled_pin(this.checked)"> '.get_string('label_activatepin', 'tals').'
            </td>
          </tr>
          
          
          <!-- ZEILE 2 -->
          <tr>
            <td class="description_cell"> 
              <p class="description">'.get_string('label_duration', 'tals').'</p>
            </td>
            <td>
              <select id="duration" name="PIN_duration" disabled="true">';

$begin = 1;
$stop = 90;
$width = 5;
$selected = 15;

for ($i = $begin; $i < $width; $i++) { 
  if ($i != $selected) {
    echo '<option value="'.$i.'">'.$i.'</option>';
  } else {
    echo '<option value="'.$i.'" selected>'.$i.'</option>';
  }
}

for ($j = $width; $j <= $stop; $j = $j + $width) { 
  if ($j != $selected) {
    echo '<option value="'.$j.'">'.$j.'</option>';
  } else {
    echo '<option value="'.$j.'" selected>'.$j.'</option>';
  }
}

echo '</select> 
      <p class="description"> '.get_string('label_minute', 'tals').'</p>
      </td>
    </tr> 
  </table>
</div>';

// End
echo '<div>
        <input type="submit" id="setpin" value="'.get_string('label_safe', 'tals').'" style="border-radius: 0.4em;">
      </div>

      </form>
  <p>* - '.get_string('label_required', 'tals').'</p>
  </div>';

echo $OUTPUT->footer();