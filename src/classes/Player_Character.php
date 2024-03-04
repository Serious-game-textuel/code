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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Character.php');
class Player_Character extends Character {

    private int $id;

    public function __construct(?int $id, string $description, string $name,
    array $status, array $items, ?Location_Interface $currentlocation) {
        if (!isset($id)) {
            $super = new Character(null, $description, $name, $status, $items, $currentlocation);
            parent::__construct($super->get_id(), "", "", [], [], null);
            global $DB;
            $this->id = $DB->insert_record('playercharacter', [
                'character' => $super->get_id(),
            ]);
        } else {
            $this->id = $id;
        }
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
        if (in_array("mort", $status)) {
            array_push($return, "Tu es mort!");
            $app->restart_game_from_start();
        } else if (in_array("victoire", $status)) {
            $game = $app->get_game();
            array_push($return, "Tu as gagné!");
            array_push($return, "Tu as fait " . $game->get_actions() . " actions!");
            array_push($return, "Tu as visité " . count($game->get_visited_locations()) . " lieux!");
            array_push($return, "Tu as été tué " . $game->get_deaths() . " fois!");
        } else {
            parent::set_status($status);
        }
        return $return;
    }
}
