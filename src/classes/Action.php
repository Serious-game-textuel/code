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
        $this->conditions = $conditions;
    }

    public function get_id() {
        return $this->id;
    }

    public function set_id(int $id) {
        $this->id = $id;
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
        $this->conditions = $conditions;
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
        }
        return $return;
    }
}
