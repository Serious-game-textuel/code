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
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Action_Interface.php');

/**
 * Class Action
 * @package mod_stg
 */
class Action implements Action_Interface {

    private int $id;

    public function __construct(?int $id, string $description, array $conditions) {
        global $DB;
        if (!isset($id)) {
            Util::check_array($conditions, Condition_Interface::class);
            $this->id = $DB->insert_record('stg_action', [
                'description' => $description,
            ]);
            foreach ($conditions as $condition) {
                $DB->insert_record('stg_action_conditions', [
                    'action_id' => $this->id,
                    'condition_id' => $condition->get_id(),
                ]);
            }
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {stg_action} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Action object of ID:".$id." exists.");
            }
            $this->id = $id;
        }
    }

    public static function get_instance(int $id) {
        return new Action($id, "", []);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_description() {
        global $DB;
        $sql = "select description from {stg_action} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->id]);
    }

    public function set_description(string $description) {
        global $DB;
        $DB->set_field('stg_action', 'description', $description, ['id' => $this->id]);
    }

    public function get_conditions() {
        $conditions = [];
        global $DB;
        $sql = "select condition_id from {stg_action_conditions} where "
        . $DB->sql_compare_text('action_id') . " = ".$DB->sql_compare_text(':id');
        $ids = $DB->get_fieldset_sql($sql, ['id' => $this->id]);
        foreach ($ids as $id) {
            array_push($conditions, Condition::get_instance($id));
        }
        return $conditions;
    }

    public function set_conditions(array $conditions) {
        $conditions = Util::clean_array($conditions, Condition_Interface::class);
        global $DB;
        $DB->delete_records('stg_action_conditions', ['action_id' => $this->id]);
        foreach ($conditions as $condition) {
            $DB->insert_record('stg_game_visitedlocations', [
                'action_id' => $this->id,
                'condition_id' => $condition->get_id(),
            ]);
        }
    }

    public function do_conditions() {
        $game = App::get_instance()->get_game();
        App::get_instance()->add_action();
        $conditions = $this->get_conditions();
        $conditionstrue = [];
        $debug = [];
        foreach ($conditions as $condition) {
            $res = $condition->is_true();
            if ($res[0]) {
                array_push($conditionstrue, $condition);
                array_push($debug, $condition->__toString().' -> true');
            } else {
                array_push($debug, $condition->__toString().' -> false ('.$res[1].')');
            }
        }
        $return = [];
        foreach ($conditionstrue as $condition) {
            array_push($return, $condition->do_reactions());
        }
        return [$return, $debug];
    }
}
