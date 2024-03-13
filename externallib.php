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
 * External services for mod_stg.
 * @package     mod_stg
 * @copyright   2024 Your Name
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/mod/stg/src/classes/App.php');

class mod_stg_external extends external_api {

    public static function get_file_info($draftitemid) {
        global $USER;
        // Check the user is logged in.
        require_login();
        $context = context_user::instance($USER->id);
        self::validate_context($context);
        // Get the file info.
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'user', 'draft', $draftitemid, 'id DESC', false);
        // Prepare the files array.
        $fileinfo = [];
        foreach ($files as $file) {
            $content = $file->get_content();
            $tempfilepath = tempnam(sys_get_temp_dir(), 'mod_stg');
            file_put_contents($tempfilepath, $content);
            $app= new App(null, $tempfilepath, 0, 0, new DateTime(), []);
            $app->delete_all_data();
            return "fichier upload";
        }
        // Return 'mauvais fichier' if no file was found.
        return 'pas de fichier';
    }

    public static function get_file_info_returns() {
        return new external_value(PARAM_TEXT, 'Result of the file check');
    }

    public static function get_file_info_parameters() {
        return new external_function_parameters(
            ['draftitemid' => new external_value(PARAM_INT, 'Draft item id')]
        );
    }
}