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
require_once($CFG->dirroot . '/mod/stg/src/classes/Character.php');

/**
 * Class Player_Character
 * @package mod_stg
 */
class Player_Character extends Character {

    private int $id;

    public function __construct(?int $id, string $description, string $name,
    array $status, array $items, ?Location_Interface $currentlocation) {
        global $DB;
        if (!isset($id)) {
            parent::__construct(null, $description, $name, $status, $items, $currentlocation);
            $this->id = $DB->insert_record('stg_playercharacter', [
                'character_id' => parent::get_id(),
            ]);
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {stg_playercharacter} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Player_Character object of ID:".$id." exists.");
            }
            $sql = "select character_id from {stg_playercharacter} where "
            . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
            $super = $DB->get_field_sql($sql, ['id' => $id]);
            parent::__construct($super, "", "", [], [], null);
            $this->id = $id;
        }
    }

    public function get_parent_id() {
        return parent::get_id();
    }

    public static function get_instance_from_parent_id(int $characterid): Player_Character {
        global $DB;
        $sql = "select id from {stg_playercharacter} where "
        . $DB->sql_compare_text('character_id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $characterid]);
        return self::get_instance($id);
    }

    public static function get_instance(int $id): Player_Character {
        return new Player_Character($id, "", "", [], [], null);
    }

    public function get_id() {
        return $this->id;
    }

    public function set_status(array $status) {
        $return = [];
        $app = App::get_instance();
        $language = $app->get_language();
        if ($language == 'fr') {
            if (in_array("mort", $status)) {
                array_push($return, "Tu es mort!");
                $app = App::get_instance();
                if ($app->get_save() !== null) {
                    App::get_instance()->restart_game_from_save();
                } else {
                    App::get_instance()->restart_game_from_start();
                }
            } else if (in_array("victoire", $status)) {
                array_push($return, "Tu as gagné!");
                array_push($return, "Tu as fait " . App::get_instance()->get_actions() . " actions!");
                array_push($return, "Tu as visité " . count(App::get_instance()->get_visited_locations()) . " lieux!");
                array_push($return, "Tu as été tué " . App::get_instance()->get_deaths() . " fois!");
            } else {
                parent::set_status($status);
            }
        } else {
            if (in_array("dead", $status)) {
                array_push($return, "You are dead!");
                $app = App::get_instance();
                if ($app->get_save() !== null) {
                    App::get_instance()->restart_game_from_save();
                } else {
                    App::get_instance()->restart_game_from_start();
                }
            } else if (in_array("victory", $status)) {
                array_push($return, "You won!");
                array_push($return, "You did " . App::get_instance()->get_actions() . " actions!");
                array_push($return, "You visited " .
                 count(App::get_instance()->get_visited_locations()) . " locations!");
                array_push($return, "You were killed " . App::get_instance()->get_deaths() . " times!");
            } else {
                parent::set_status($status);
            }
        }
        return $return;
    }

    public function set_currentlocation(Location_Interface $newlocation) {
        parent::set_currentlocation($newlocation);
        App::get_instance()->add_visited_location($newlocation);
    }

}
