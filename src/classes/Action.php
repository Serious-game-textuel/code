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
    private Entity_Interface $entity1;
    private Entity_Interface $entity2;
    private string $connector;
    private array $conditions;

    public function __construct(Entity_Interface $entity1, Entity_Interface $entity2, string $connector,
     array $conditions) {
        $this->id = Id_Class::generate_id(Action::class);
        $this->entity1 = $entity1;
        $this->entity2 = $entity2;
        $this->connector = $connector;
        $this->conditions = $conditions;
    }

    public function get_id() {
        return $this->id;
    }

    public function set_id(int $id) {
        $this->id = $id;
    }

    public function get_entity1() {
        return $this->entity1;
    }

    public function set_entity1(Entity_Interface $entity1) {
        $this->entity1 = $entity1;
    }

    public function get_entity2() {
        return $this->entity2;
    }

    public function set_entity2(Entity_Interface $entity2) {
        $this->entity2 = $entity2;
    }

    public function get_connector() {
        return $this->connector;
    }

    public function set_connector(string $connector) {
        $this->connector = $connector;
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
        foreach ($conditionstrue as $condition) {
            $condition->do_reactions();
        }
    }
}
