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
 * Web service definition
 *
 * @package    mod_tals
 * @copyright  2017 Technische Hochschule Mittelhessen - University of Applied Sciences - Giessen, Germany
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'mod_wstals_get_todays_appointments' => array(
        'classname'   => 'mod_wstals_external',
        'methodname'  => 'get_todays_appointments',
        'classpath'   => 'mod/tals/externallib.php',
        'description' => 'Method that retrieves a list of todays appointments.',
        'type'        => 'read'),
    'mod_wstals_insert_attendance' => array(
        'classname'   => 'mod_wstals_external',
        'methodname'  => 'insert_attendance',
        'classpath'   => 'mod/tals/externallib.php',
        'description' => 'Method that sets the attendies attendance to Present',
        'type'        => 'write'),
    'mod_wstals_check_for_enabled_pin' => array(
        'classname'   => 'mod_wstals_external',
        'methodname'  => 'check_for_enabled_pin',
        'classpath'   => 'mod/tals/externallib.php',
        'description' => 'Method that checks if the pin of given appointment is available.',
        'type'        => 'read'),
    'mod_wstals_get_days_absent' => array(
        'classname'   => 'mod_wstals_external',
        'methodname'  => 'get_days_absent',
        'classpath'   => 'mod/tals/externallib.php',
        'description' => 'Method that return count of days absent.',
        'type'        => 'read'),
    'mod_wstals_get_courses' => array(
        'classname'   => 'mod_wstals_external',
        'methodname'  => 'get_courses',
        'classpath'   => 'mod/tals/externallib.php',
        'description' => 'Method that return list of courses the user is enrolled in.',
        'type'        => 'read')
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array('THM Attendance Logging System' => array(
            'functions' => array(
                'mod_wstals_get_todays_appointments',
                'mod_wstals_insert_attendance',
                'mod_wstals_check_for_enabled_pin',
                'mod_wstals_get_days_absent',
                'mod_wstals_get_courses'),
            'restrictedusers' => 0,
            'enabled'         => 1,
            'shortname'       => 'tals'
        )
    );