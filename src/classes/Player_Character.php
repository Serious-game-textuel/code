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

/**
 * Class Player_Character
 * @package mod_serioustextualgame
 */
class Player_Character extends Character {


    public function set_status(array $status) {
        $return = [];
        $app = App::get_instance();
        $language = $app->get_language();

        if (in_array(App::$deadkeyword, $status)) {
            if ($language == Language::FR) {
                array_push($return, "Tu es mort!");
            } else {
                array_push($return, "You are dead!");
            }
            $app = App::get_instance();
            if ($app->get_save() !== null) {
                App::get_instance()->restart_game_from_save();
            } else {
                App::get_instance()->restart_game_from_start();
            }
        } else if (in_array(App::$victorykeyword, $status)) {
            if ($language == Language::FR) {
                array_push($return, "Tu as gagné!");
                array_push($return, "Tu as fait " . App::get_instance()->get_game()->get_actions() . " actions!");
                array_push($return, "Tu as visité " . count(App::get_instance()->get_game()->get_visited_locations()) . " lieux!");
                array_push($return, "Tu as été tué " . App::get_instance()->get_game()->get_deaths() . " fois!");
            } else {
                array_push($return, "You won!");
                array_push($return, "You did " . App::get_instance()->get_game()->get_actions() . " actions!");
                array_push($return, "You visited " .
                 count(App::get_instance()->get_game()->get_visited_locations()) . " locations!");
                array_push($return, "You were killed " . App::get_instance()->get_game()->get_deaths() . " times!");
            }
        } else {
            parent::set_status($status);
        }
        return $return;
    }
}
