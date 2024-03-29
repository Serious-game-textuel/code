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
require_once($CFG->dirroot . '/mod/stg/src/classes/Condition.php');

/**
 * Class Leaf_Condition
 * @package mod_stg
 * @copyright   2024 Paul Grandhomme, Loric Gallier, Benjamin Bracquier, Mathis Courant
 */
class Leaf_Condition extends Condition {

    private ?int $id;

    public function __construct(?int $id, ?Entity_Interface $entity1, ?Entity_Interface $entity2,
    string $connector, ?array $status, array $reactions) {
        global $DB;
        if (!isset($id)) {
            Util::check_array($status, 'string');
            parent::__construct(null, $reactions);
            $arguments = [
                'condition_id' => parent::get_id(),
                'connector' => $connector,
            ];
            if (isset($entity1)) {
                $arguments['entity1_id'] = $entity1->get_id();
            }
            if (isset($entity2)) {
                $arguments['entity2_id'] = $entity2->get_id();
            }
            $this->id = $DB->insert_record('stg_leafcondition', $arguments);
            foreach ($status as $statut) {
                $DB->insert_record('stg_leafcondition_status', [
                    'leafcondition_id' => $this->id,
                    'status' => $statut,
                ]);
            }
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {stg_leafcondition} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Leaf_Condition object of ID:".$id." exists.");
            }
            $sql = "select condition_id from {stg_leafcondition} where "
            . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
            $super = $DB->get_field_sql($sql, ['id' => $id]);
            parent::__construct($super, []);
            $this->id = $id;
        }
    }

    public function get_parent_id() {
        return parent::get_id();
    }

    public static function get_instance_from_parent_id(int $conditionid): Leaf_Condition {
        global $DB;
        $sql = "select id from {stg_leafcondition} where "
        . $DB->sql_compare_text('condition_id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $conditionid]);
        return self::get_instance($id);
    }

    public static function get_instance(int $id): Leaf_Condition {
        return new Leaf_Condition($id, null, null, "", null, []);
    }

    public function get_entity1() {
        global $DB;
        $sql = "select entity1_id from {stg_leafcondition} where ".
        $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $this->id]);
        if ($id > 0) {
            return Entity::get_instance($id);
        } else {
            return null;
        }
    }

    public function set_entity1(Entity_Interface $entity1) {
        global $DB;
        $DB->set_field('stg_leafcondition', 'entity1_id', $entity1->get_id(), ['id' => $this->id]);
    }

    public function get_entity2() {
        global $DB;
        $sql = "select entity2_id from {stg_leafcondition} where ". $DB->sql_compare_text('id') . " = ".
        $DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $this->id]);
        if ($id > 0) {
            return Entity::get_instance($id);
        } else {
            return null;
        }
    }

    public function set_entity2(Entity_Interface $entity2) {
        global $DB;
        $DB->set_field('stg_leafcondition', 'entity2_id', $entity2->get_id(), ['id' => $this->id]);
    }

    public function get_connector() {
        global $DB;
        $sql = "select connector from {stg_leafcondition} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->id]);
    }

    public function set_connector(string $connector) {
        global $DB;
        $DB->set_field('stg_leafcondition', 'connector', $connector, ['id' => $this->id]);
    }

    public function get_status() {
        global $DB;
        $sql = "select status from {stg_leafcondition_status} where "
        . $DB->sql_compare_text('leafcondition_id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_fieldset_sql($sql, ['id' => $this->id]);
    }

    public function set_status(array $status) {
        $status = Util::clean_array($status, 'string');
        global $DB;
        $DB->delete_records('stg_leafcondition_status', ['leafcondition_id' => $this->id]);
        foreach ($status as $statut) {
            $DB->insert_record('stg_leafcondition_status', [
                'leafcondition_id' => $this->id,
                'status' => $statut,
            ]);
        }
    }

    public function is_true(): array {
        $entity1 = $this->get_entity1();
        $entity2 = $this->get_entity2();
        $connector = $this->get_connector();
        $status = $this->get_status();
        $app = App::get_instance();
        $language = $app->get_language();
        if ($entity1 == null && $entity2 == null && $connector == "" && $status == null) {
            return [true, ""];
        }
        global $DB;
        if ($language == "fr") {
            if ($entity1 != null) {
                $entity1status = $entity1->get_status();
                try {
                    $character1 = Character::get_instance_from_parent_id($entity1->get_id());
                    if ($entity2 != null) {
                        try {
                            $item2 = Item::get_instance_from_parent_id($entity2->get_id());
                            if ($connector == "possède" || $connector == "a") {
                                $return = $character1->has_item_character($item2);
                                if ($return) {
                                    return [$return, ""];
                                } else {
                                    return [$return, $character1->get_name().' a pas '.$item2->get_name()];
                                }
                            } else if ($connector == "possède pas" || $connector == "a pas") {
                                $return = !$character1->has_item_character($item2);
                                if ($return) {
                                    return [$return, ""];
                                } else {
                                    return [$return, $character1->get_name().' a '.$item2->get_name()];
                                }
                            }
                        } catch (Exception $e) {
                            $e;
                        }
                    } else {
                        if ($connector == "est") {
                            foreach ($status as $s) {
                                if (!in_array($s, $entity1status)) {
                                    return [false, $character1->get_name().' est pas '.$s];
                                }
                            }
                            return [true, ""];
                        } else if ($connector == "est pas") {
                            foreach ($status as $s) {
                                if (in_array($s, $entity1status)) {
                                    return [false, $character1->get_name().' est '.$s];
                                }
                            }
                            return [true, ""];
                        }
                    }
                } catch (Exception $e) {
                    try {
                        $item1 = Item::get_instance_from_parent_id($entity1->get_id());
                        if ($entity2 == null) {
                            if ($connector == "est") {
                                foreach ($status as $s) {
                                    if (!in_array($s, $entity1status)) {
                                        return [false, $item1->get_name().' est pas '.$s];
                                    }
                                }
                                return [true, ""];
                            } else if ($connector == "est pas") {
                                foreach ($status as $s) {
                                    if (in_array($s, $entity1status)) {
                                        return [false, $item1->get_name().' est '.$s];
                                    }
                                }
                                return [true, ""];
                            }
                        }
                    } catch (Exception $e) {
                        try {
                            $location1 = Location::get_instance_from_parent_id($entity1->get_id());
                            if ($entity2 == null) {
                                if ($connector == "est") {
                                    foreach ($status as $s) {
                                        if (!in_array($s, $entity1status)) {
                                            return [false, $location1->get_name().' est pas '.$s];
                                        }
                                    }
                                    return [true, ""];
                                } else if ($connector == "est pas") {
                                    foreach ($status as $s) {
                                        if (in_array($s, $entity1status)) {
                                            return [false, $location1->get_name().' est '.$s];
                                        }
                                    }
                                    return [true, ""];
                                }
                            } else {
                                try {
                                    $item2 = item::get_instance_from_parent_id($entity2->get_id());
                                    if ($connector == "possède" || $connector == "a") {
                                        $return = $location1->has_item_location($item2);
                                        if ($return) {
                                            return [true, ""];
                                        } else {
                                            return [false, $location1->get_name().' a pas '.$item2->get_name()];
                                        }
                                    } else if ($connector == "possède pas" || $connector == "a pas") {
                                        $return = !$location1->has_item_location($item2);
                                        if ($return) {
                                            return [true, ""];
                                        } else {
                                            return [false, $location1->get_name().' a '.$item2->get_name()];
                                        }
                                    }
                                } catch (Exception $e) {
                                    $e;
                                }
                            }
                        } catch (Exception $e) {
                            $e;
                        }
                    }
                }
            }
        } else {
            if ($entity1 != null) {
                $entity1status = $entity1->get_status();
                try {
                    $character1 = Character::get_instance_from_parent_id($entity1->get_id());
                    if ($entity2 != null) {
                        try {
                            $item2 = Item::get_instance_from_parent_id($entity2->get_id());
                            if ($connector == "has" || $connector == "have") {
                                $return = $character1->has_item_character($item2);
                                if ($return) {
                                    return [$return, ""];
                                } else {
                                    return [$return, $character1->get_name().' has not '.$item2->get_name()];
                                }
                            } else if ($connector == "has not" || $connector == "have not") {
                                $return = !$character1->has_item_character($item2);
                                if ($return) {
                                    return [$return, ""];
                                } else {
                                    return [$return, $character1->get_name().' has '.$item2->get_name()];
                                }
                            }
                        } catch (Exception $e) {
                            $e;
                        }
                    } else {
                        if ($connector == "is") {
                            foreach ($status as $s) {
                                if (!in_array($s, $entity1status)) {
                                    return [false, $character1->get_name().' is not '.$s];
                                }
                            }
                            return [true, ""];
                        } else if ($connector == "is not") {
                            foreach ($status as $s) {
                                if (in_array($s, $entity1status)) {
                                    return [false, $character1->get_name().' is '.$s];
                                }
                            }
                            return [true, ""];
                        }
                    }
                } catch (Exception $e) {
                    try {
                        $item1 = Item::get_instance_from_parent_id($entity1->get_id());
                        if ($entity2 == null) {
                            if ($connector == "is") {
                                foreach ($status as $s) {
                                    if (!in_array($s, $entity1status)) {
                                        return [false, $item1->get_name().' is not '.$s];
                                    }
                                }
                                return [true, ""];
                            } else if ($connector == "is not") {
                                foreach ($status as $s) {
                                    if (in_array($s, $entity1status)) {
                                        return [false, $item1->get_name().' is '.$s];
                                    }
                                }
                                return [true, ""];
                            }
                        }
                    } catch (Exception $e) {
                        try {
                            $location1 = Location::get_instance_from_parent_id($entity1->get_id());
                            if ($entity2 == null) {
                                if ($connector == "is") {
                                    foreach ($status as $s) {
                                        if (!in_array($s, $entity1status)) {
                                            return [false, $location1->get_name().' is not '.$s];
                                        }
                                    }
                                    return [true, ""];
                                } else if ($connector == "is not") {
                                    foreach ($status as $s) {
                                        if (in_array($s, $entity1status)) {
                                            return [false, $location1->get_name().' is '.$s];
                                        }
                                    }
                                    return [true, ""];
                                }
                            } else {
                                try {
                                    $item2 = item::get_instance_from_parent_id($entity2->get_id());
                                    if ($connector == "has" || $connector == "have") {
                                        $return = $location1->has_item_location($item2);
                                        if ($return) {
                                            return [true, ""];
                                        } else {
                                            return [false, $location1->get_name().' has not '.$item2->get_name()];
                                        }
                                    } else if ($connector == "has not" || $connector == "have not") {
                                        $return = !$location1->has_item_location($item2);
                                        if ($return) {
                                            return [true, ""];
                                        } else {
                                            return [false, $location1->get_name().' has '.$item2->get_name()];
                                        }
                                    }
                                } catch (Exception $e) {
                                    $e;
                                }
                            }
                        } catch (Exception $e) {
                            $e;
                        }
                    }
                }
            }
        }
        return [false, "Error in condition"];
    }

    public function get_id() {
        return $this->id;
    }

    public function __toString() {
        $entity1 = $this->get_entity1();
        $entity2 = $this->get_entity2();
        $connector = $this->get_connector();
        $status = $this->get_status();
        $return = '(';
        if (isset($entity1)) {
            $return = $return."'".$entity1->get_name()."' ".$connector.' ';
            if (isset($entity2)) {
                $return = $return."'".$entity2->get_name()."'";
            } else {
                $return = $return.'['.implode(', ', $status).']';
            }
        }
        return $return.')';
    }
}

