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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Entity.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Location_Interface.php');
class Location extends Entity implements Location_Interface {

    private Inventory_Interface $inventory;
    private array $hints;
    private array $actions;

    public function __construct(string $name, array $status, array $items, array $hints, array $actions) {
        parent::__construct("", $name, $status);
        $this->inventory = new Inventory($items);
        $this->hints = $hints;
        $this->actions = $actions;
    }
    public function get_inventory() {
        return $this->inventory;
    }

    public function get_actions() {
        return $this->actions;
    }
    public function get_hints() {
        return $this->hints;
    }

    public function is_action_valide(string $action) {
        for ($i = 0; $i < count($this->actions); $i++) {
            if ($this->actions[$i]->get_description() == $action) {
                return $action[$i];
            }
        }
        return null;
    }

    public function check_actions(string $action) {
        $return = [];
        $game = Game::getinstance();
        $action = App::tokenize($action);
        $actionvalide = $this->is_action_valide($action);
        if ($actionvalide != null) {
            array_push($return, $actionvalide->do_conditions());
        } else {
            $defaultaction = "fouiller";
            if (strpos($action, $defaultaction) === 0) {
                $entity = substr($action, strlen($defaultaction) + 1);
                if ($game->get_entity($entity) !== null) {
                    if ($game->get_default_action_interact() !== null) {
                        $result = $game->get_default_action_interact()->do_conditions_verb($defaultaction);
                        echo($result);
                        array_push($return, $result);
                    } else {
                        if ($game->get_default_action_search() !== null) {
                            $result = $game->get_default_action_search()->do_conditions_verb($defaultaction);
                            echo($result);
                            array_push($return, $result);
                        }
                    }
                } else {
                    if ($game->get_default_action_interact() !== null) {
                        $result = $game->get_default_action_interact()->do_conditions_verb($defaultaction);
                        echo($result);
                        array_push($return, $result);
                    }
                }

            }
        }
        return $return;
    }

    public function has_item_location(Item_Interface $item) {
        return $this->inventory->check_item($item);
    }
}
