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

class Condition implements Condition_Interface {

    private int $id;
    private array $reactions;

    public function __construct(int $id, array $reactions) {
        $this->id = $id;
        $this->reactions = $reactions;

    }

    public function get_id() {
        return $this->id;
    }

    public function set_id(int $id) {
        $this->id = $id;
    }

    public function get_reactions() {
        return $this->reactions;
    }

    public function set_reactions(array $reactions) {
        $this->reactions = $reactions;
    }

    public function do_reactions() {
        $reactions = $this->get_reactions();
        foreach ($reactions as $reaction) {
            if ($reaction instanceof Character_Reaction) {
                if ($reaction->get_character() != null) {
                    $character = $reaction->get_character();
                    if ($reaction->get_new_location() != null) {
                        $newlocation = $reaction->get_new_location();
                        $game = Game::getinstance();
                        $game->set_current_location($newlocation);
                        $game->add_visited_location($newlocation);
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
            return $reaction->get_description();
        }
        return "pas de réaction";
    }

    public function is_true() {
        if ($this instanceof Leaf_Condition) {
            $entity1 = $this->get_entity1();
            $entity2 = $this->get_entity2();
            $connector = $this->get_connector();
            $status = $this->get_status();
            $entity1status = $entity1->get_status();
            $entity2status = $entity2->get_status();
            if ($entity1 instanceof Character) {
                if ($entity2 == null) {
                    if ($connector == "est") {
                        return $entity1status == $status;
                    } else if ($connector == "est pas") {
                        return $entity1status != $status;
                    }
                } else if ($entity2 instanceof Item) {
                    if ($connector == "possède") {
                        return $entity1->has_item_character($entity2);
                    } else if ($connector == "possède pas") {
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
                    if ($connector == "possède") {
                        return $entity1->has_item_location($entity2);
                    } else if ($connector == "possède pas") {
                        return !$entity1->has_item_location($entity2);
                    }
                }
            }
        } else if ($this instanceof Node_Condition) {
            $condition1 = $this->get_condition1();
            $condition2 = $this->get_condition2();
            $connector1 = $this->get_connector1();
            if ($connector1 == "AND") {
                return $condition1->is_true() && $condition2->is_true();
            } else if ($connector1 == "OR") {
                return $condition1->is_true() || $condition2->is_true();
            }
            return false;
        }
        return false;
    }

}
