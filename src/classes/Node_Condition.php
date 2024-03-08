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
class Node_Condition extends Condition {

    private int $id;

    public function __construct(?int $id, ?Condition_Interface $condition1, ?Condition_Interface $condition2,
    string $connector, ?array $reactions) {
        global $DB;
        if (!isset($id)) {
            parent::__construct(null, $reactions);
            $this->id = $DB->insert_record('nodecondition', [
                'condition_id' => parent::get_id(),
                'condition1_id' => $condition1->get_id(),
                'condition2_id' => $condition2->get_id(),
                'connector' => $connector,
            ]);
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {nodecondition} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Node_Condition object of ID:".$id." exists.");
            }
            $sql = "select condition_id from {nodecondition} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
            $super = $DB->get_field_sql($sql, ['id' => $id]);
            parent::__construct($super, []);
            $this->id = $id;
        }
    }

    public function get_parent_id() {
        return parent::get_id();
    }

    public static function get_instance_from_parent_id(int $conditionid): Node_Condition {
        global $DB;
        $sql = "select id from {nodecondition} where "
        . $DB->sql_compare_text('condition_id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $conditionid]);
        return Node_Condition::get_instance($id);
    }

    public static function get_instance(int $id): Node_Condition {
        return new Node_Condition($id, null, null, "", null);
    }

    public function get_condition1() {
        global $DB;
        $sql = "select condition1_id from {nodecondition} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return Condition::get_instance($DB->get_field_sql($sql, ['id' => $this->get_id()]));
    }
    public function get_condition2() {
        global $DB;
        $sql = "select condition2_id from {nodecondition} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return Condition::get_instance($DB->get_field_sql($sql, ['id' => $this->get_id()]));
    }
    public function get_connector() {
        global $DB;
        $sql = "select connector from {nodecondition} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->get_id()]);
    }
    public function set_condition(Condition_Interface $condition1) {
        global $DB;
        $DB->set_field('nodecondition', 'condition1_id', $condition1->get_id(), ['id' => $this->get_id()]);
    }
    public function set_condition2(Condition_Interface $condition2) {
        global $DB;
        $DB->set_field('nodecondition', 'condition2_id', $condition2->get_id(), ['id' => $this->get_id()]);
    }
    public function set_connector(string $connector) {
        global $DB;
        $DB->set_field('nodecondition', 'connector', $connector, ['id' => $this->get_id()]);
    }

    public function is_true(): array {
        $condition1 = $this->get_condition1();
        $condition2 = $this->get_condition2();
        $connector = $this->get_connector();
        if ($connector == "&") {
            $res1 = $condition1->is_true();
            $res2 = $condition2->is_true();
            return [$res1[0] && $res2[0], $res1[1].' '.$res2[1]];
        } else if ($connector == "|") {
            $res1 = $condition1->is_true();
            $res2 = $condition2->is_true();
            return [$res1[0] || $res2[0], $res1[1].' '.$res2[1]];
        }
        return [false, 'condition error : wrong connector'];
    }

    public function get_id() {
        return $this->id;
    }

    public function __toString() {
        return '('.$this->get_condition1()->__toString().' '
        .$this->get_connector().' '.$this->get_condition1()->__toString().')';
    }
}

