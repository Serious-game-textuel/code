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

    private ?int $id;

    public function __construct(?int $id, ?Entity_Interface $entity1, ?Entity_Interface $entity2,
    string $connector, ?array $status, array $reactions) {
        if (!isset($id)) {
            Util::check_array($status, 'string');
            $super = new Condition(null, $reactions);
            parent::__construct($super->get_id(), []);
            global $DB;
            $this->id = $DB->insert_record('leafcondition', [
                'condition' => $super->get_id(),
                'entity1' => $entity1->get_id(),
                'entity2' => $entity2->get_id(),
                'connector' => $connector,
            ]);
            foreach ($status as $statut) {
                $DB->insert_record('leafcondition_status', [
                    'leafcondition' => $this->id,
                    'status' => $statut,
                ]);
            }
        } else {
            $this->id = $id;
        }
    }

    public static function get_instance(int $id) {
        return new Leaf_Condition($id, null, null, "", null, []);
    }

    public function get_entity1() {
        global $DB;
        $sql = "select entity1 from {leafcondition} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $this->get_id()]);
        return Entity::get_instance($id);
    }

    public function set_entity1(Entity_Interface $entity1) {
        global $DB;
        $DB->set_field('leafcondition', 'entity1', $entity1->get_id(), ['id' => $this->get_id()]);
    }

    public function get_entity2() {
        global $DB;
        $sql = "select entity2 from {leafcondition} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $this->get_id()]);
        return Entity::get_instance($id);
    }

    public function set_entity2(Entity_Interface $entity2) {
        global $DB;
        $DB->set_field('leafcondition', 'entity2', $entity2->get_id(), ['id' => $this->get_id()]);
    }

    public function get_connector() {
        global $DB;
        $sql = "select connector from {leafcondition} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->get_id()]);
    }

    public function set_connector(string $connector) {
        global $DB;
        $DB->set_field('leafcondition', 'connector', $connector, ['id' => $this->get_id()]);
    }

    public function get_status() {
        global $DB;
        $sql = "select status from {leafcondition_status} where "
        . $DB->sql_compare_text('leafcondition') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_fieldset_sql($sql, ['id' => $this->get_id()]);
    }

    public function set_status(array $status) {
        $status = Util::clean_array($status, 'string');
        global $DB;
        $DB->delete_records('leafcondition_status', ['leafcondition' => $this->get_id()]);
        foreach ($status as $statut) {
            $DB->insert_record('leafcondition_status', [
                'leafcondition' => $this->id,
                'location' => $statut,
            ]);
        }
    }

    public function is_true() {
        $entity1 = $this->get_entity1();
        $entity2 = $this->get_entity2();
        $connector = $this->get_connector();
        $status = $this->get_status();
        global $DB;
        if ($entity1 != null) {
            $entity1status = $entity1->get_status();
        }
        $ischaracter = $DB->record_exists_sql(
            "SELECT id FROM {character} WHERE "
            .$DB->sql_compare_text('entity')." = ".$DB->sql_compare_text(':id'),
            ['id' => $entity1->get_id()]
        );
        $isitem = $DB->record_exists_sql(
            "SELECT id FROM {item} WHERE "
            .$DB->sql_compare_text('entity')." = ".$DB->sql_compare_text(':id'),
            ['id' => $entity1->get_id()]
        );
        $islocation = $DB->record_exists_sql(
            "SELECT id FROM {location} WHERE "
            .$DB->sql_compare_text('entity')." = ".$DB->sql_compare_text(':id'),
            ['id' => $entity1->get_id()]
        );
        if ($ischaracter) {
            $isitem = $DB->record_exists_sql(
                "SELECT id FROM {item} WHERE "
                .$DB->sql_compare_text('entity')." = ".$DB->sql_compare_text(':id'),
                ['id' => $entity2->get_id()]
            );
            if ($entity2 == null) {
                if ($connector == "est") {
                    return $entity1status == $status;
                } else if ($connector == "est pas") {
                    return $entity1status != $status;
                }
            } else if ($isitem) {
                if ($connector == "possède" || $connector == "a") {
                    return $entity1->has_item_character($entity2);
                } else if ($connector == "possède pas" || $connector == "a pas") {
                    return !$entity1->has_item_character($entity2);
                }
            }
        } else if ($isitem) {
            if ($entity2 == null) {
                if ($connector == "est") {
                    return $entity1status == $status;
                } else if ($connector == "est pas") {
                    return $entity1status != $status;
                }
            }
        } else if ($islocation) {
            $isitem = $DB->record_exists_sql(
                "SELECT id FROM {item} WHERE "
                .$DB->sql_compare_text('entity')." = ".$DB->sql_compare_text(':id'),
                ['id' => $entity2->get_id()]
            );
            if ($entity2 == null) {
                if ($connector == "est") {
                    return $entity1status == $status;
                } else if ($connector == "est pas") {
                    return $entity1status != $status;
                }
            } else if ($isitem) {
                if ($connector == "possède" || $connector == "a") {
                    return $entity1->has_item_location($entity2);
                } else if ($connector == "possède pas" || $connector == "a pas") {
                    return !$entity1->has_item_location($entity2);
                }
            }

        } else if ($entity1 == null && $entity2 == null && $connector == "" && $status == null) {
            return true;
        }
        return false;
    }

    public function get_id() {
        return $this->id;
    }
}

