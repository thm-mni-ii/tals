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
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     mod_tals
 * @copyright   2017 Technische Hochschule Mittelhessen - University of Applied Sciences - Giessen, Germany
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_tals_install() {
  global $DB;

  $result = true;

  // set the default attendance types
  $arr = array('Present', 'Absent', 'Excused'); // Don't change this array unless you know what you do.

  foreach ($arr as $k) {
    $record = new stdClass;

    $record->acronym = get_string($k.'_acronym', 'tals');
    $record->description = get_string($k.'_full', 'tals');

    $result = $result && $DB->insert_record('tals_type_attendance', $record);
  }

  // set the default appointment types
  $arr = array('Lecture', 'Excercise', 'Seminar', 'Training', 'Other');

  foreach ($arr as $k) {
    $record = new stdClass;

    $record->title = get_string($k.'_full', 'tals');
    $record->acronym = get_string($k.'_acronym', 'tals');

    $result = $result && $DB->insert_record('tals_type_appointment', $record);
  }

  // set the default networks
  $arr =  array(
            array('fh-giessen.de', 1), 
            array('its.thm.de', 2),
            array('vpn.thm.de', 2)
          );

  foreach ($arr as $k) {
    $record = new stdClass;

    $record->host = $k[0];
    $record->acceptance = $k[1];

    $result = $result && $DB->insert_record('tals_type_net', $record);
  }

  return $result;
}