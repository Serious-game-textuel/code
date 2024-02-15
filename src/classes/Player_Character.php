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


    public function set_status(array $status) {
        if (in_array("mort", $status)) {
            echo "Tu es mort!";
            $app = App::get_instance();
            if ($app->get_save() !== null) {                
                App::get_instance()->restart_game_from_save();
            } else {
                App::get_instance()->restart_game_from_start();
            }
            
        } else if (in_array("victoire", $status)) {
            echo "Tu as gagné!";  
            echo "tu est mort " . App::get_instance()->get_game()->get_deaths() . " fois!";
            echo "Tu as fait " . App::get_instance()->get_game()->get_actions() . " actions!";    
            echo "Tu as visité " . count(App::get_instance()->get_game()->get_visited_locations()) . " lieux!";

        } else {
            parent::set_status($status);
        }
    }
}
