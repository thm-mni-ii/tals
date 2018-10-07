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
 * Block is defined here.
 *
 * @package     block_tals
 * @copyright   2018 Lars Herwegh <lars.herwegh@mni.thm.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (file_exists(__DIR__.'/../../mod/tals/locallib.php')) {
  require_once(__DIR__.'/../../mod/tals/locallib.php');
}


class block_tals extends block_base {
  public function init() {
    $this->title = get_string('blocktitle', 'block_tals');
  }

  public function instance_allow_multiple() {
    return false;
  }

  public function has_config() {
    return false;
  }

  public function applicable_formats() {
    return array(
              'my' => false,
              'course-view' => true,
              'mod-tals' => true,
              'mod' => false,
              'user' => false
              );
  }

  public function get_content() {
    global $DB, $COURSE, $USER;

    $sudo = false;
    $dbman = $DB->get_manager();
 
    $this->content = new stdClass;

    if (!$dbman->table_exists('tals')) {
      $this->content->text = '<div class="alert-info">'.get_string('instance', 'block_tals').'</div>';
      return $this->content;
    }

    $tals = get_all_instances_in_course('tals', $COURSE, null, true);

    if (count($tals) == 0) {
      $this->content->text = '<div class="alert-info">'.get_string('instance', 'block_tals').'</div>';
      return $this->content;
    }

    $cmid;
    $cm;
    $context;

    foreach ($tals as $entry) {
      $cmid = $entry->coursemodule;
      $cm  = get_coursemodule_from_id('tals', $cmid, $COURSE->id, false, MUST_EXIST);
      $context = context_module::instance($cmid, MUST_EXIST);
      break;
    }

    // check manager capabilities
    $capabilities = array(
        'mod/tals:manage',
        'mod/tals:change',
        'mod/tals:viewreports'
    );

    if (has_any_capability($capabilities, $context)) {
      $sudo = true;
    }

    $this->content->text = '<style>
      .rahmen {border: 1px solid #ddd;padding: 4px;margin: 5px;padding-top: 10px;text-align: center;}

      .description {display:inline;}

      .description_cell {width:150px;}

      .block {background-color: white;height: 100%; width: 100%;text-align: center;}

      #tals_label {color: #80ba24;font-family: arial;font-size: 2em;margin: 0;}

      #text_pin {margin-bottom: 1em;width: 150px;text-align: center;}

      #infoText {color: #4a5c66;font-family: arial;font-size: 1.5em;}

      #infoTextSmall {color: #4a5c66;font-family: arial;font-size: 1em;}

      #farbe_rot {color: #971b2f;font-family: arial;}

      #farbe_gruen {color: #80ba24;font-family: arial;}

      #tabelle {margin: 0px auto;}

      #tabelle2 {margin: 0px auto;text-align: center;}

      #PIN_data {margin-left: 1em;}

      #button {margin-top: 1em;}

      .button {width: 150px;}

      #successSign {font-size: 2em;color: #80ba24;margin:0;}
    </style>';

    $list = tals_get_current_appointments($COURSE->id);
    $content = "";
    $userstatus = tals_get_attendance_count_for_user($USER->id, $COURSE->id);

    if (empty($list)) {
      $next = tals_get_next_appointment($COURSE->id);

      if (!empty($next)) {
        $content .= '<div class="rahmen">
                      <p id="infoText">'.get_string('label_noapp', 'block_tals').'</p>
                      <p id="infoTextSmall"><b>'.get_string('label_nextapp', 'block_tals').':</b><br>
                      '.$next->title.' ('.$next->type.')<br>
                      '.date('d.m.Y', $next->start).', '.date('H:i', $next->start).' - '.date('H:i', $next->end).'</p>
                    </div>';
      } else {
        $content .= '<div class="rahmen">
                <p id="infoText">'.get_string('label_noapp', 'block_tals').'</p>
              </div>';
      }
    } else {
      if ($sudo) {
        foreach ($list as $entry) {
          $content .= '<div class="rahmen">
                          <p id="infoText">'.$entry->title.'</p>
                          <p id="infoTextSmall">'.$entry->type.'<br>
                          '.date('H:i', $entry->start).' - '.date('H:i', $entry->end).'</p>';

          if (!is_null($entry->pin)) {
            if (tals_check_for_enabled_pin($entry->id)) {
              $content .= '<p id="infoText">'.get_string('label_pin', 'block_tals').': '.$entry->pin.' <img src="/moodle/blocks/tals/pix/show.png" alt="'.get_string('label_show', 'block_tals').'" height="15" width="15"></p>
                <p id="infoTextSmall">'.get_string('label_until', 'block_tals').' '.date('H:i', $entry->pinuntil).' '.get_string('label_hour', 'block_tals').'</p>
                <p id="infoTextSmall">'.get_string('label_count', 'block_tals').': '.count(tals_get_logs_for_course($COURSE->id, $entry->id, PRESENT)).'</p>'; // TODO : reload-img einfügen
            } else {
              $content .= '<p id="infoText">'.get_string('label_pin', 'block_tals').': '.$entry->pin.' <a href="'.new moodle_url('/blocks/tals/enablepin.php', array('id' => $cmid, 'appid' => $entry->id)).'"><img src="/moodle/blocks/tals/pix/hide.png" alt="'.get_string('label_hide', 'block_tals').'" height="15" width="15"></a></p>
                <p id="infoTextSmall">('.$entry->pindur.' '.get_string('label_minute', 'block_tals').')</p>';
            }
          }
          
          $content .= '</div>';
        }
      } else {
        foreach ($list as $entry) {
          $content .= '<div class="rahmen">
                          <p id="infoText">'.$entry->title.'</p>
                          <p id="infoTextSmall">'.$entry->type.'<br>
                          '.date('H:i', $entry->start).' - '.date('H:i', $entry->end).'</p>';

          if (tals_is_user_already_attending($entry->id, $USER->id)) {
            $content .= '<p id="infoTextSmall"><img src="/moodle/blocks/tals/pix/success.png" alt="'.get_string('label_alreadyattending', 'block_tals').'" height="60" width="60"><br>
                        '.get_string('label_alreadyattending', 'block_tals').'</p>';
          } else {
            if (!is_null($entry->pin)) {
              if (tals_check_for_enabled_pin($entry->id)) {
                $content .= '<p id="infoTextSmall">'.get_string('label_until', 'block_tals').' '.date('H:i', $entry->pinuntil).' '.get_string('label_hour', 'block_tals').'</p>';
        
                $content .= '<form action="'.new moodle_url('/blocks/tals/insertattendance.php', array('id' => $cmid, 'userid' => $USER->id, 'appid' => $entry->id)).'" method="post" id="formular">
                      <div class="block" id="PIN" class="tabcontent" display="none">
                        <br>
                        <input id="text_pin" placeholder="'.get_string('label_pinexample', 'block_tals').'" autofocus required title="'.get_string('label_pinformat', 'block_tals').'" type="text" name="pin" pattern="[0-9]{4}" maxlength="4">
                        <br>
                        <input type="checkbox" name="absentdays" value="absentdays" required="true"> <p id="infoTextSmall" style="display:inline;"> '.get_string('label_acceptmissed_first', 'block_tals').' '.$userstatus->absent.' '.get_string('label_acceptmissed_last', 'block_tals').'<p>
                        <br>
                        <input type="submit" class="button" name="submit" value="'.get_string('label_enlist', 'block_tals').'">
                      </div>
                    </form>';
              } else {
                $content .= '<p id="infoTextSmall">'.get_string('label_pinnotenabled', 'block_tals').'</p>';
              }
            }
          }

          $content .= '</div>';
        }
      }
    }

    $this->content->text .= $content;

    return $this->content;
  }
}