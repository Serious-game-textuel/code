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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Condition_Interface.php');

/**
 * Class Condition
 * @package mod_serioustextualgame
 */
class Condition implements Condition_Interface {

    private int $id;
    private array $reactions = [];

    public function __construct(array $reactions) {
        Util::check_array($reactions, Reaction_Interface::class);
        $this->id = Id_Class::generate_id(self::class);
        $this->reactions = $reactions;
    }

    public function get_id() {
        return $this->id;
    }

    public function get_reactions() {
        return $this->reactions;
    }

    public function set_reactions(array $reactions) {
        $this->reactions = Util::clean_array($reactions, Reaction_Interface::class);
    }

    public function do_reactions() {
        $app = App::get_instance();
        $game = $app->get_game();
        $language = $app->get_language();
        $reactions = $this->get_reactions();
        $descriptions = [];
        foreach ($reactions as $reaction) {
            if ($reaction instanceof Character_Reaction) {
                if ($reaction->get_character() != null) {
                    $character = $reaction->get_character();

                    if ($reaction->get_new_location() != null) {
                        $newlocation = $reaction->get_new_location();
                        if ($character instanceof Npc_Character) {
                            $character->set_currentlocation($newlocation);
                        } else if ($character instanceof Player_Character) {
                            $game->set_current_location($newlocation);
                            $game->add_visited_location($newlocation);
                            $descriptionreturn = $newlocation->check_actions("description");

                        }
                    }
                    if ($reaction->get_new_item() != null) {
                        $newitems = $reaction->get_new_item();
                        foreach ($newitems as $item) {
                            $character->get_inventory()->add_item($item);
                        }
                    }
                    if ($reaction->get_old_item() != null) {
                        $olditem = $reaction->get_old_item();
                        foreach ($olditem as $item) {
                            $character->get_inventory()->remove_item($item);
                        }
                    }
                    if ($reaction->get_new_status() != null) {
                        $newstatus = $reaction->get_new_status();
                        $character->add_status($newstatus);
                        if ($character instanceof Player_Character) {
                            foreach ($newstatus as $status) {
                                if ($language == "fr") {
                                    if ($status == "mort") {
                                        $game->add_deaths();
                                        echo App::$playerkeyword . " est mort, recommmencer au début(pas implémenté)\n";
                                    }
                                } else {
                                    if ($status == "dead") {
                                        $game->add_deaths();
                                        echo App::$playerkeyword . " is dead, you can restart (not implemented)\n";
                                    }
                                }
                            }
                            if ($language == "fr") {
                                if ($newstatus == "victoire") {
                                    $deaths = $game->get_deaths();
                                    $starttime = $game->get_start_time();
                                    $endtime = new DateTime();
                                    $interval = $starttime->diff($endtime);
                                    $time = $interval->format('%H:%I:%S');
                                    $lieux = $game->get_visited_locations();
                                    return "Vous avez gagné en " . $time . " avec " . $deaths
                                    . " morts et " . count($lieux) . " lieux visités.";
                                }
                            } else {
                                if ($newstatus == "victory") {
                                    $deaths = $game->get_deaths();
                                    $starttime = $game->get_start_time();
                                    $endtime = new DateTime();
                                    $interval = $starttime->diff($endtime);
                                    $time = $interval->format('%H:%I:%S');
                                    $lieux = $game->get_visited_locations();
                                    return "You won in " . $time . " with " . $deaths
                                    . " deaths and " . count($lieux) . " visited locations.";
                                }
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
                        $newitems = $reaction->get_new_item();
                        foreach ($newitems as $item) {
                            $location->get_inventory()->add_item($item);
                        }
                    }
                    if ($reaction->get_old_item() != null) {
                        $olditem = $reaction->get_old_item();
                        foreach ($olditem as $item) {
                            $location->get_inventory()->remove_item($item);
                        }
                    }
                }
            }
            array_push($descriptions, $reaction->get_description());
        }
        if (empty($descriptions)) {
            if ($language == "fr") {
                return "pas de réaction";
            } else {
                return "no reaction";
            }
        }
        if (isset($descriptionreturn[0])) {
            array_push($descriptions, $descriptionreturn[0]);
        }
        return implode(' / ', $descriptions);
    }

    public function is_true() {
        $app = App::get_instance();
        $language = $app->get_language();
        if ($language == "fr") {
            if ($this instanceof Leaf_Condition) {
                $entity1 = $this->get_entity1();
                $entity2 = $this->get_entity2();
                $connector = $this->get_connector();
                $status = $this->get_status();

                if ($entity1 != null) {
                    $entity1status = $entity1->get_status();
                }

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

                } else if ($entity1 == null && $entity2 == null && $connector == "" && $status == null) {
                    return true;
                }
            } else if ($this instanceof Node_Condition) {
                $condition1 = $this->get_condition1();
                $condition2 = $this->get_condition2();
                $connector = $this->get_connector();
                if ($connector == "&") {
                    return $condition1->is_true() && $condition2->is_true();
                } else if ($connector == "|") {
                    return $condition1->is_true() || $condition2->is_true();
                }
                return false;
            }
        } else {
            if ($this instanceof Leaf_Condition) {
                $entity1 = $this->get_entity1();
                $entity2 = $this->get_entity2();
                $connector = $this->get_connector();
                $status = $this->get_status();

                if ($entity1 != null) {
                    $entity1status = $entity1->get_status();
                }

                if ($entity1 instanceof Character) {
                    if ($entity2 == null) {
                        if ($connector == "is") {
                            return $entity1status == $status;
                        } else if ($connector == "is not") {
                            return $entity1status != $status;
                        }
                    } else if ($entity2 instanceof Item) {
                        if ($connector == "has") {
                            return $entity1->has_item_character($entity2);
                        } else if ($connector == "has not") {
                            return !$entity1->has_item_character($entity2);
                        }
                    }
                } else if ($entity1 instanceof Item) {
                    if ($entity2 == null) {
                        if ($connector == "is") {
                            return $entity1status == $status;
                        } else if ($connector == "is not") {
                            return $entity1status != $status;
                        }
                    }
                } else if ($entity1 instanceof Location) {
                    if ($entity2 == null) {
                        if ($connector == "is") {
                            return $entity1status == $status;
                        } else if ($connector == "is not") {
                            return $entity1status != $status;
                        }
                    } else if ($entity2 instanceof Item) {
                        if ($connector == "has") {
                            return $entity1->has_item_location($entity2);
                        } else if ($connector == "has not") {
                            return !$entity1->has_item_location($entity2);
                        }
                    }

                } else if ($entity1 == null && $entity2 == null && $connector == "" && $status == null) {
                    return true;
                }
            } else if ($this instanceof Node_Condition) {
                $condition1 = $this->get_condition1();
                $condition2 = $this->get_condition2();
                $connector = $this->get_connector();
                if ($connector == "&") {
                    return $condition1->is_true() && $condition2->is_true();
                } else if ($connector == "|") {
                    return $condition1->is_true() || $condition2->is_true();
                }
                return false;
            }
        }
        return false;
    }
}
