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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Action_Interface.php');

class Action implements Action_Interface {

    private int $id;
    private string $description;
    private array $conditions;

    public function __construct(string $description, array $conditions) {
        $this->id = Id_Class::generate_id(self::class);
        $this->description = $description;
        Util::check_array($conditions, Condition_Interface::class);
        $this->conditions = $conditions;
    }

    public function get_id() {
        return $this->id;
    }

    public function get_description() {
        return $this->description;
    }

    public function set_description(string $description) {
        $this->description = $description;
    }

    public function get_conditions() {
        return $this->conditions;
    }

    public function set_conditions(array $conditions) {
        $this->conditions = Util::clean_array($conditions, Condition_Interface::class);
    }

    public function do_conditions() {
        $game = Game::getinstance();
        $game->add_action();
        $conditions = $this->get_conditions();
        $conditionstrue = [];
        foreach ($conditions as $condition) {
            if ($condition->is_true()) {
                array_push($conditionstrue, $condition);
            }
        }
        $return = [];
        if (count($conditionstrue) > 0) {
            foreach ($conditionstrue as $condition) {
                $result = $condition->do_reactions();
                echo($result);
                array_push($return, $result);
            }
        } else {
            $tokens = explode(' ', App::tokenize($this->description));
            if ($tokens[0] == "fouiller") {
                if ($game->get_entity($tokens[1]) !== null) {
                    if ($game->get_default_action_search() !== null) {
                        $result = $game->get_default_action_search()->do_conditions_verb($tokens[0]);
                        echo($result);
                        array_push($return, $result);
                    }
                } else {
                    if ($game->get_default_action_interact() !== null) {
                        $result = $game->get_default_action_interact()->do_conditions_verb($tokens[0]);
                        echo($result);
                        array_push($return, $result);
                    }
                }
            } else {
                if ($game->get_default_action_interact() !== null) {
                    $result = $game->get_default_action_interact()->do_conditions_verb($tokens[0]);
                    echo($result);
                        array_push($return, $result);
                }
            }
        }
        return $return;
    }
}
