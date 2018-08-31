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
 * @package     block_tals
 * @copyright   2017 Lars Herwegh <lars.herwegh@mni.thm.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/../../mod/tals/locallib.php');
require_once($CFG->libdir.'/accesslib.php');

// Course_module ID, or
$id = required_param('id', PARAM_INT);
$userid  = required_param('userid', PARAM_INT);
$appid  = required_param('appid', PARAM_INT);
$pin  = optional_param('pin', null, PARAM_INT);
$isok = optional_param('isok', false, PARAM_BOOL);

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

$PAGE->set_url('/blocks/tals/insertattendance.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));

// CSS
$css = '<style type="text/css">

        #alertBox{margin:0 auto;border: solid 10px #B80040;padding: 10px;text-align: center;height: -moz-fit-content;width: -moz-fit-content;border-radius: 5px;}

        #successBox{margin:0 auto;border: solid 10px #80BA24;padding: 10px;text-align: center;height: -moz-fit-content;width: -moz-fit-content;border-radius: 5px;}

        #buttonBox {text-align: center;}

        #buttonOK {width: 20%;}

        .text {font-family: arial;}

    </style>';

// TODO : prüfen, ob PIN noch enabled ist. Wenn nicht, abbrechen

if ($isok && is_null($pin)) { // success
    echo $OUTPUT->header();

    echo $css;

    echo '<div id="successBox">
            <p class="text">
                <img src="/moodle/blocks/tals/pix/success.png" alt="'.get_string('label_success', 'block_tals').'"><br>
                <b>'.get_string('label_attendancesuccess', 'block_tals').'</b>
            </p>
            <div id="buttonBox">
                <a style="text-decoration: none;" href="'.new moodle_url('/course/view.php', array('id' => $course->id)).'"><input type="button" id="buttonOK" name="ok" value="'.get_string('label_ok', 'block_tals').'"></a>
            </div>
        </div>';

    echo $OUTPUT->footer();
} else if (!$isok && is_null($pin)) { // failure
    echo $OUTPUT->header();

    echo $css;

    echo '<div id="alertBox">
            <p class="text">
                <img src="/moodle/blocks/tals/pix/failure.png" alt="'.get_string('label_failure', 'block_tals').'"><br>
                <b>'.get_string('label_attendancefailure', 'block_tals').'</b>
            </p>
            <div id="buttonBox">
                <a style="text-decoration: none;" href="'.new moodle_url('/course/view.php', array('id' => $course->id)).'"><input type="button" id="buttonOK" name="ok" value="'.get_string('label_ok', 'block_tals').'"></a>
            </div>
        </div>';

    echo $OUTPUT->footer();
} else { // insert
    $params = array('id' => $id);
    $acceptance = tals_get_type_net();

    $att = tals_update_attendance($userid, "", PRESENT, $acceptance, $appid, $pin);

    if ($att > SUCCESS) {
        $params = array('id' => $id, 'userid' => $userid, 'appid' => $appid, 'isok' => true);
    } else {
        $params = array('id' => $id, 'userid' => $userid, 'appid' => $appid, 'isok' => false);
    }

    redirect(new moodle_url('/blocks/tals/insertattendance.php', $params));
}
