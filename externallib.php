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
 * @category    services
 * @copyright   2024 Your Name 
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");

class mod_serioustextualgame_external extends external_api {

    public static function get_file_info($draftitemid) {
        global $USER;
    
        // Check the user is logged in
        require_login();
        $context = context_user::instance($USER->id);
        self::validate_context($context);
    
        // Get the file info
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'user', 'draft', $draftitemid, 'id DESC', false);
    
        // Prepare the files array
        $fileinfo = array();
        foreach ($files as $file) {
            $fileinfo[] = array(
                'id' => $file->get_id(),
                'name' => $file->get_filename(),
                'content' => $file->get_content(),
                // Add other file properties here
            );
        }
    
        // Return the file info
        return [
            'files' => $fileinfo,
        ];
    }
    
    

    public static function get_file_info_returns() {
        return new external_single_structure([
            'files' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'ID of the file'),
                    'name' => new external_value(PARAM_TEXT, 'Name of the file'),
                    'content' => new external_value(PARAM_RAW, 'Content of the file'),
                    // Add other file properties here
                ])
            ),
        ]);
    }
    

    public static function get_file_info_parameters() {
        return new external_function_parameters(
            array('draftitemid' => new external_value(PARAM_INT, 'Draft item id'))
        );
    }
    
}