<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants.
 *
 * @package     mod_stg
 * @copyright   2024 Paul Grandhomme, Loric Gallier, Benjamin Bracquier, Mathis Courant
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function stg_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_stg into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_stg_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function stg_add_instance($moduleinstance, $mform = null) {
    global $DB;
    $moduleinstance->timecreated = time();
    $filecontent = $mform->get_file_content('userfile');
    if ($filecontent) {
        $moduleinstance->filecontent = $filecontent;
    }
    $draftitemid = file_get_submitted_draft_itemid('imagefile');

    $context = context_module::instance($moduleinstance->coursemodule);
    file_save_draft_area_files($draftitemid, $context->id, 'mod_stg', 'imagefile', 0);

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_stg', 'imagefile', 0, 'itemid, filepath, filename', false);
    if (count($files) > 0) {
        $file = reset($files);
        $moduleinstance->fileid = $file->get_id();
    }
    $id = $DB->insert_record('stg', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_stg in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_stg_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function stg_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;
    $filecontent = $mform->get_file_content(
        'userfile'
    );
    if ($filecontent) {
        $moduleinstance->filecontent = $filecontent;
    }

    $draftitemid = file_get_submitted_draft_itemid('imagefile');

    $context = context_module::instance($moduleinstance->coursemodule);
    file_save_draft_area_files($draftitemid, $context->id, 'mod_stg', 'imagefile', 0);

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_stg', 'imagefile', 0, 'itemid, filepath, filename', false);
    if (count($files) > 0) {
        $file = reset($files);
        $moduleinstance->fileid = $file->get_id();
    }

    return $DB->update_record('stg', $moduleinstance);
}


/**
 * Removes an instance of the mod_stg from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function stg_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('stg', ['id' => $id]);
    if (!$exists) {
        return false;
    }

    $DB->delete_records('stg', ['id' => $id]);

    return true;
}

/**
 * Returns the lists of all browsable file areas within the given module context.
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@see file_browser::get_file_info_context_module()}.
 *
 * @package     mod_stg
 * @category    files
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return string[].
 */
function stg_get_file_areas($course, $cm, $context) {
    return [];
}

/**
 * File browsing support for mod_stg file areas.
 *
 * @package     mod_stg
 * @category    files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info Instance or null if not found.
 */
function stg_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the mod_stg file areas.
 *
 * @package     mod_stg
 * @category    files
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The mod_stg's context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 */
function stg_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = []) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);
    if (!has_capability('mod/stg:view', $context)) {
        return false;
    }
    if ($filearea !== 'content' && $filearea !== 'imagefile') {
        return false;
    }
    array_shift($args);
    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_stg/imagefile/0/$relativepath";
    $file = $fs->get_file_by_hash(sha1($fullpath));
    if (!$file || $file->is_directory()) {
        return false;
    }

    // Set security posture for in-browser display.
    if (!$forcedownload) {
        header("Content-Security-Policy: default-src 'none'; img-src 'self'; media-src 'self'");
    }

    // Finally send the file.
    send_stored_file($file, 0, 0, $forcedownload, $options);
}
