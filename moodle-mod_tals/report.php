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

$start = 1514764800;
$end = strtotime(date('d-m-Y H:i', time()));
$list = tals_get_appointments($course->id, $start, $end);

$context = new stdClass();

$context->addurl =  new moodle_url('/mod/tals/add.php', ['id' => $id]);
$context->manageurl =  new moodle_url('/mod/tals/manage.php', ['id' => $id]);

$context->downloadurl = new moodle_url('/mod/tals/reportdownload.php', ['id' => $id]);
$context->entries = array();

foreach($list as $val) {
    $entry = new stdClass();
    $entry->id = $val->id;
    $entry->title = $val->title;
    $entry->description = $val->description;
    $entry->type = $val->type;
    $entry->detailurl = new moodle_url('/mod/tals/reportdetail.php', ['id' => $id, 'appid' => $val->id]);

    $entry->startdate = date('d.m.Y, H:i', $val->start);
    $entry->enddate = date('d.m.Y, H:i', $val->ending);
    $entry->duration = $val->duration;
    $entry->presentcount = count(tals_get_logs_for_course($course->id, $val->id, PRESENT));
    $entry->logcount = $entry->presentcount + count(tals_get_logs_for_course($course->id, $val->id, EXCUSED)) + count(tals_get_logs_for_course($course->id, $val->id, ABSENT));
    $context->entries[] = $entry;
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('tals/report', $context);
echo $OUTPUT->footer();