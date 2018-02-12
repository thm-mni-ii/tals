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
 * @copyright   2017 Lars Herwegh <lars.herwegh@mni.thm.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/locallib.php');
require_once($CFG->libdir.'/accesslib.php');

// Course_module ID, or
$id = required_param('id', PARAM_INT);
$userid  = required_param('userid', PARAM_INT);
$appid  = required_param('appid', PARAM_INT);
$courseid  = required_param('courseid', PARAM_INT);
$attendance = optional_param('attendance', 0, PARAM_INT);
$comment = optional_param('comment', "", PARAM_TEXT);

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

$PAGE->set_url('/mod/tals/editstudent.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

tals_update_attendance($userid, $comment, $attendance, 3, $appid, null, true);

redirect(new moodle_url('/mod/tals/reportdetail.php', array('id' => $id, 'appid' => $appid)));