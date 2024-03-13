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
require_once($CFG->dirroot . '/mod/stg/src/classes/Reaction.php');

/**
 * Class Location_Reaction
 * @package mod_stg
 * @copyright   2024 Paul Grandhomme, Loric Gallier, Benjamin Bracquier, Mathis Courant
 */
class Location_Reaction extends Reaction {

    private int $id;

    public function __construct(?int $id, string $description, array $oldstatus,
    array $newstatus, array $olditem, array $newitem, ?Location_Interface $location) {
        global $DB;
        if (!isset($id)) {
            parent::__construct(null, $description, $oldstatus, $newstatus, $olditem, $newitem);
            $this->id = $DB->insert_record('stg_locationreaction', [
                'reaction_id' => parent::get_id(),
                'location_id' => $location->get_id(),
            ]);
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {stg_locationreaction} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Location_Reaction object of ID:".$id." exists.");
            }
            $sql = "select reaction_id from {stg_locationreaction} where "
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
        $sql = "select location_id from {stg_locationreaction} where "
        . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return Location::get_instance($DB->get_field_sql($sql, ['id' => $this->id]));
    }

    public static function get_instance_from_parent_id(int $reactionid): Location_Reaction {
        global $DB;
        $sql = "select id from {stg_locationreaction} where "
        . $DB->sql_compare_text('reaction_id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $reactionid]);
        return self::get_instance($id);
    }

    public static function get_instance(int $id): Location_Reaction {
        return new Location_Reaction($id, "", [], [], [], [], null);
    }

    public function get_id() {
        return $this->id;
    }

    public function do_reactions(): array {
        $return = [];
        $location = $this->get_location();
        if ($location != null) {
            $reactionparent = Reaction::get_instance($this->get_parent_id());
            $newstatus = $reactionparent->get_new_status();
            if ($newstatus != null) {
                $location->add_status($newstatus);
            }
            $oldstatus = $reactionparent->get_old_status();
            if ($oldstatus != null) {
                $location->remove_status($oldstatus);
            }
            $newitems = $reactionparent->get_new_item();
            if ($newitems != null) {
                foreach ($newitems as $item) {
                    $location->get_inventory()->add_item($item);
                }
            }
            $olditem = $reactionparent->get_old_item();
            if ($olditem != null) {
                foreach ($olditem as $item) {
                    $location->get_inventory()->remove_item($item);
                }
            }
        }
        return $return;
    }

}

