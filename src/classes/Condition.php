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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Game.php');
class Condition implements Condition_Interface {

    private int $id;
    private array $reactions = [];

    public function __construct(array $reactions) {
        for ($i=0; $i<sizeof($reactions); $i++) {
            if (!$reactions[$i] instanceof Reaction_Interface) {
                $reactions[$i] = null;
            }
        }
        $this->id = Id_Class::generate_id(self::class);
        $this->reactions = array_filter($reactions);

    }

    public function get_id() {
        return $this->id;
    }

    public function get_reactions() {
        return $this->reactions;
    }

    public function set_reactions(array $reactions) {
        for ($i=0; $i<sizeof($reactions); $i++) {
            if (!$reactions[$i] instanceof Reaction_Interface) {
                $reactions[$i] = null;
            }
        }
        $this->reactions = array_filter($reactions);
    }

    public function do_reactions() {
        $reactions = $this->get_reactions();
        $descriptions = [];
        foreach ($reactions as $reaction) {
            if ($reaction instanceof Character_Reaction) {
                if ($reaction->get_character() != null) {
                    $character = $reaction->get_character();

                    if ($reaction->get_new_location() != null) {
                        $newlocation = $reaction->get_new_location();
                        $game = Game::getinstance();
                        if ($character instanceof Npc_Character) {
                            $oldlocation = $character->get_current_location();
                            $oldlocation->remove_npc_character($character);
                            $newlocation->add_npc_character($character);
                            $character->set_new_location($newlocation);

                        } else if ($character instanceof Player_Character) {
                            $game->set_current_location($newlocation);
                            $game->add_visited_location($newlocation);
                            $newlocation->check_actions("description");
                        }
                    }
                    if ($reaction->get_new_item() != null) {
                        $newitem = $reaction->get_new_item();
                        $character->get_inventory()->add_item($newitem);
                    }
                    if ($reaction->get_old_item() != null) {
                        $olditem = $reaction->get_old_item();
                        $character->get_inventory()->remove_item($olditem);
                    }
                    if ($reaction->get_new_status() != null) {
                        $newstatus = $reaction->get_new_status();
                        $character->add_status($newstatus);
                        if ($character instanceof Player_Character) {
                            if ($newstatus == "mort") {
                                $game = Game::getinstance();
                                $game->add_deaths();
                            }
                            if ($newstatus == "victoire") {
                                $game = Game::getinstance();
                                $deaths = $game->get_deaths();
                                $starttime = $game->get_start_time();
                                $endtime = new DateTime();
                                $interval = $starttime->diff($endtime);
                                $time = $interval->format('%H:%I:%S');
                                $lieux = $game->get_visited_locations();
                                $lieuxvisites = 0;
                                foreach ($lieux as $lieu) {
                                        $lieuxvisites++;
                                }
                                return "Vous avez gagné en " . $time . " avec " . $deaths
                                . " morts et " . $lieuxvisites . " lieux visités.";
                            }
                        }
                    }
                    if ($reaction->get_old_status() != null) {
                        $oldstatus = $reaction->get_old_status();
                        $character->remove_status($oldstatus);
                    }
                }
            } else if ($reaction instanceof Location_Reaction) {
                if ($reaction->get_location() != null) {
                    $location = $reaction->get_location();
                    if ($reaction->get_new_status() != null) {
                        $newstatus = $reaction->get_new_status();
                        $location->add_status($newstatus);
                    }
                    if ($reaction->get_old_status() != null) {
                        $oldstatus = $reaction->get_old_status();
                        $location->remove_status($oldstatus);
                    }
                    if ($reaction->get_new_item() != null) {
                        $newitem = $reaction->get_new_item();
                        $location->get_inventory()->add_item($newitem);
                    }
                    if ($reaction->get_old_item() != null) {
                        $olditem = $reaction->get_old_item();
                        $location->get_inventory()->remove_item($olditem);
                    }
                }
            }
            array_push($descriptions, $reaction->get_description());
        }
        if (empty($descriptions)) {
            return "pas de réaction";
        }
        return implode(' / ', $descriptions);
    }

    public function is_true() {
        if ($this instanceof Leaf_Condition) {
            $entity1 = $this->get_entity1();
            $entity2 = $this->get_entity2();
            $connector = $this->get_connector();
            $status = $this->get_status();
            $entity1status = $entity1->get_status();
            if ($entity1 instanceof Character) {
                if ($entity2 == null) {
                    if ($connector == "est") {
                        return $entity1status == $status;
                    } else if ($connector == "est pas") {
                        return $entity1status != $status;
                    }
                } else if ($entity2 instanceof Item) {
                    if ($connector == "possède" || $connector == "a") {
                        return $entity1->has_item_character($entity2);
                    } else if ($connector == "possède pas" || $connector == "a pas") {
                        return !$entity1->has_item_character($entity2);
                    }
                }
            } else if ($entity1 instanceof Item) {
                if ($entity2 == null) {
                    if ($connector == "est") {
                        return $entity1status == $status;
                    } else if ($connector == "est pas") {
                        return $entity1status != $status;
                    }
                }
            } else if ($entity1 instanceof Location) {
                if ($entity2 == null) {
                    if ($connector == "est") {
                        return $entity1status == $status;
                    } else if ($connector == "est pas") {
                        return $entity1status != $status;
                    }
                } else if ($entity2 instanceof Item) {
                    if ($connector == "possède" || $connector == "a") {
                        return $entity1->has_item_location($entity2);
                    } else if ($connector == "possède pas" || $connector == "a pas") {
                        return !$entity1->has_item_location($entity2);
                    }
                }
            }
        } else if ($this instanceof Node_Condition) {
            $condition1 = $this->get_condition1();
            $condition2 = $this->get_condition2();
            $connector1 = $this->get_connector1();
            if ($connector1 == "et") {
                return $condition1->is_true() && $condition2->is_true();
            } else if ($connector1 == "ou") {
                return $condition1->is_true() || $condition2->is_true();
            }
            return false;
        }
        return false;
    }

}
