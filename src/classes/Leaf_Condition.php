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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Condition.php');
class Leaf_Condition extends Condition {

    private ?Entity_Interface $entity1;
    private ?Entity_Interface $entity2;
    private string $connector;
    private ?array $status;

    public function __construct(?Entity_Interface $entity1, ?Entity_Interface $entity2, string $connector,
    ?array $status, array $reactions) {
        Util::check_array($reactions, Reaction_Interface::class);
        parent::__construct($reactions);
        $this->entity1 = $entity1;
        $this->entity2 = $entity2;
        $this->connector = $connector;
        if ($status !== null) {
            Util::check_array($status, 'string');
            $status = array_filter($status);
        }
        $this->status = $status;
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

    public function get_status() {
        return $this->status;
    }

    public function set_status(array $status) {
        $this->status = Util::clean_array($status, 'string');
    }

    public function is_true() {
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
    }

}

