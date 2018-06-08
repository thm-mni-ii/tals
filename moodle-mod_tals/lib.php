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
 * Library of interface functions and constants.
 *
 * @package     mod_tals
 * @copyright   2017 Technische Hochschule Mittelhessen - University of Applied Sciences - Giessen, Germany
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports given feature.
 *
 * @param feature - Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function tals_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of tals into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param tals - an object from the form.
 * @param mform - form
 * @return id of the newly inserted record.
 */
function tals_add_instance($tals, $mform = null) {
    global $DB;

    $tals->timemodified = time();

    $tals->id = $DB->insert_record('tals', $tals);

    return $tals->id;
}

/**
 * Updates an instance of the mod_tals in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param moduleinstance - an object from the form in mod_form.php.
 * @param mform - form.
 * @return true if successful, false otherwise.
 */
function tals_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('tals', $moduleinstance);
}

/**
 * Removes an instance of the mod_tals from the database.
 *
 * @param id - id of the module instance.
 * @return true if successful, false on failure.
 */
function tals_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('tals', ['id' => $id]);
    if (!$exists) {
        return false;
    }

    $DB->delete_records('tals', ['id' => $id]);

    return true;
}
