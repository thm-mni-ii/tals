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

$list = tals_get_all_appointments_of_course($course->id);

foreach ($list as $entry) {
    $entry->reportdetailurl = new moodle_url('/mod/tals/reportdetail.php', ['id' => $id, 'appid' => $entry->id]);
    $entry->startdate = date('d.m.Y, H:i', $entry->start);
    $entry->enddate = date('d.m.Y, H:i', $entry->ending);
    $entry->haspin = !is_null($entry->pin);
    $entry->pinactive = tals_check_for_enabled_pin($entry->id);
    $entry->pinuntildate = date('H:i', $entry->pinuntil);
    $entry->enablepinurl = new moodle_url('/mod/tals/enablepin.php', ['id' => $id, 'appid' => $entry->id]);
    $entry->changeurl = new moodle_url('/mod/tals/change.php', ['id' => $id, 'appid' => $entry->id]);
    $entry->deleteurl = new moodle_url('/mod/tals/delete.php', ['id' => $id, 'appid' => $entry->id]);
    $entry->futuredate = false;
    $entry->pastdate = false;
    $entry->notnow = false;

    if($entry->haspin) {
        $now = strtotime(date('d-m-Y H:i:s', time()));
        if ($now < $entry->start) {
            $entry->futuredate = true;
            $entry->notnow = true;
        } else if ($now > $entry->ending) {
            $entry->pastdate = true;
            $entry->notnow = true;
        }
    }
}

$context = new stdClass();
$context->addurl =  new moodle_url('/mod/tals/add.php', ['id' => $id]);
$context->reporturl =  new moodle_url('/mod/tals/report.php', ['id' => $id]);
$context->entries = $list;

echo $OUTPUT->render_from_template('tals/manage', $context);
echo $OUTPUT->footer();