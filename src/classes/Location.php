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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Location_Interface.php');
class Location extends Entity implements Location_Interface {

    private Inventory_Interface $inventory;
    private array $hints;
    private array $actions;

    public function __construct(string $name, array $status, array $items, array $hints, array $actions) {
        Util::check_array($status, 'string');
        parent::__construct("", $name, $status);
        Util::check_array($items, Item_Interface::class);
        $this->inventory = new Inventory($items);
        $this->hints = $hints;
        Util::check_array($actions, Action_Interface::class);
        $this->actions = $actions;
    }
    public function get_inventory() {
        return $this->inventory;
    }
    public function get_actions() {
        return $this->actions;
    }
    public function set_actions(array $actions) {
        $this->actions = $actions;
    }
    public function get_hints() {
        return $this->hints;
    }

    public function is_action_valide(string $action) {
        for ($i = 0; $i < count($this->actions); $i++) {
            if ($this->actions[$i]->get_description() == $action) {
                return $this->actions[$i];
            }
        }
        return null;
    }

    public function check_actions(string $action) {
        $return = [];
        $game = App::get_instance()->get_game();
        $app = App::get_instance();
        $app->store_actionsdone($action);
        $action = App::tokenize($action);
        $actionvalide = $this->is_action_valide($action);
        if ($actionvalide != null) {
            $result = $actionvalide->do_conditions();
            foreach ($result as $res) {
                array_push($return, $res);
            }
        } else {
            $defaultaction = "fouiller";
            if (strpos($action, $defaultaction) === 0) {
                $entity = substr($action, strlen($defaultaction) + 1);
                if ($game->get_entity($entity) !== null) {
                    if ($game->get_default_action_interact() !== null) {
                        $result = $game->get_default_action_interact()->do_conditions_verb($defaultaction);
                        foreach ($result as $res) {
                            array_push($return, $res);
                        }
                    } else {
                        if ($game->get_default_action_search() !== null) {
                            $result = $game->get_default_action_search()->do_conditions_verb($defaultaction);
                            foreach ($result as $res) {
                                array_push($return, $res);
                            }
                        }
                    }
                } else {
                    if ($game->get_default_action_interact() !== null) {
                        $result = $game->get_default_action_interact()->do_conditions_verb($defaultaction);
                        foreach ($result as $res) {
                            array_push($return, $res);
                        }
                    } else {
                        array_push($return, "je n'ai pas compris ce que tu voulais ".$defaultaction);
                    }
                }
            } else if ($action == "sauvegarder") {
                App::get_instance()->create_save();
                array_push($return, "Partie sauvegardée");
            } else if ($action == "indices") {
                $hints = $this->get_hints();
                $descriptions = [];
                foreach ($hints as $hint) {
                    $descriptions[] = $hint->get_description();
                }
                $descriptionsstring = implode(' / ', $descriptions);
                array_push($return, $descriptionsstring);
            } else if ($action == "sortie") {
                array_push($return, $this->get_exit());
            } else {
                $firstword = explode(' ', $action)[0];
                if ($game->get_default_action_interact() !== null) {
                    $result = $game->get_default_action_interact()->do_conditions_verb($firstword);
                    foreach ($result as $res) {
                        array_push($return, $res);
                    }
                } else {
                    array_push($return, $action.'? Tu ne peux pas faire ca.');
                }
            }
        }
        return $return;
    }

    public function has_item_location(Item_Interface $item) {
        return $this->inventory->check_item($item);
    }

    public function get_exit() {
        $sortie = "Sorties disponibles : ";
        foreach ($this->actions as $action) {
            $conditions = $action->get_conditions();
            foreach ($conditions as $condition) {
                $reactions = $condition->get_reactions();
                foreach ($reactions as $reaction) {
                    if ($reaction instanceof Character_Reaction) {
                        if ($reaction->get_new_location() != null && $reaction->get_character() instanceOf Player_Character) {
                            $description = explode(" ", $action->get_description());
                            $sortie .= implode(' ', array_slice($description, 1)).", ";
                        }
                    }
                }
            }
        }
        return rtrim($sortie, " ,");
    }
}
