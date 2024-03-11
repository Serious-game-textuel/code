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
 * External services for mod_serioustextualgame.
 * @package     mod_serioustextualgame
 * @copyright   2024 Your Name
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");

class mod_serioustextualgame_external extends external_api {

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
            // Parse the CSV content.
            $lines = str_getcsv($content, "\n");
            if (count($lines) > 1) {
                $secondline = str_getcsv($lines[1]);
                if (count($secondline) > 1) {
                    // Get the second column of the second line.
                    $secondcolumnvalue = $secondline[1];
                    // Check if the second column value is 'coucou'.
                    if ($secondcolumnvalue === 'coucou') {
                        return 'bon fichier';
                    } else {
                        return 'mauvais fichier';
                    }
                }
            }
        }
        // Return 'mauvais fichier' if no file was found.
        return 'mauvais fichier';
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
