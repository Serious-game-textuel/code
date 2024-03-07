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
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Reaction.php');
class Location_Reaction extends Reaction {

    private int $id;

    public function __construct(?int $id, string $description, array $oldstatus,
    array $newstatus, array $olditem, array $newitem, ?Location_Interface $location) {
        global $DB;
        if (!isset($id)) {
            parent::__construct(null, $description, $oldstatus, $newstatus, $olditem, $newitem);
            $this->id = $DB->insert_record('locationreaction', [
                'reaction_id' => parent::get_id(),
                'location_id' => $location->get_id(),
            ]);
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {locationreaction} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Location_Reaction object of ID:".$id." exists.");
            }
            $sql = "select reaction_id from {locationreaction} where "
            . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
            $super = $DB->get_field_sql($sql, ['id' => $id]);
            parent::__construct($super, "", [], [], [], []);
            $this->id = $id;
        }
    }

    public function get_parent_id() {
        return parent::get_id();
    }

    public function get_location(): Location_Interface {
        global $DB;
        $sql = "select location_id from {locationreaction} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return Location_Interface::get_instance($DB->get_field_sql($sql, ['id' => $this->get_id()]));
    }

    public static function get_instance_from_parent_id(int $reactionid): Location_Reaction {
        global $DB;
        $sql = "select id from {locationreaction} where "
        . $DB->sql_compare_text('reaction_id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $reactionid]);
        return Location_Reaction::get_instance($id);
    }

    public static function get_instance(int $id): Location_Reaction {
        return new Location_Reaction($id, "", [], [], [], [], null);
    }

    public function get_id() {
        return $this->id;
    }

}

