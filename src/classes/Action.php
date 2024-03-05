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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Action_Interface.php');

class Action implements Action_Interface {

    private int $id;

    public function __construct(?int $id, string $description, array $conditions) {
        if (!isset($id)) {
            Util::check_array($conditions, Condition_Interface::class);
            global $DB;
            $this->id = $DB->insert_record('action', [
                'description' => $description,
            ]);
            foreach ($conditions as $condition) {
                $DB->insert_record('action_conditions', [
                    'action' => $this->id,
                    'condition' => $condition->get_id(),
                ]);
            }
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {action} WHERE "
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
        $sql = "select description from {action} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->get_id()]);
    }

    public function set_description(string $description) {
        global $DB;
        $DB->set_field('description', 'action', $description, ['id' => $this->get_id()]);
    }

    public function get_conditions() {
        $conditions = [];
        global $DB;
        $sql = "select condition from {action_conditions} where "
        . $DB->sql_compare_text('action') . " = ".$DB->sql_compare_text(':id');
        $ids = $DB->get_fieldset_sql($sql, ['id' => $this->get_id()]);
        foreach ($ids as $id) {
            array_push($conditions, Condition::get_instance($id));
        }
        return $conditions;
    }

    public function set_conditions(array $conditions) {
        $conditions = Util::clean_array($conditions, Condition_Interface::class);
        global $DB;
        $DB->delete_records('action_conditions', ['action' => $this->get_id()]);
        foreach ($conditions as $condition) {
            $DB->insert_record('game_visitedlocations', [
                'action' => $this->id,
                'condition' => $condition->get_id(),
            ]);
        }
    }

    public function do_conditions() {
        $app = App::get_instance();
        $game = $app->get_game();
        $game->add_action();
        $conditions = $this->get_conditions();
        $conditionstrue = [];
        foreach ($conditions as $condition) {
            if ($condition->is_true()) {
                array_push($conditionstrue, $condition);
            }
        }
        $return = [];
        if (count($conditionstrue) > 0) {
            foreach ($conditionstrue as $condition) {
                $result = $condition->do_reactions();
                array_push($return, $result);
            }
        }
        return $return;
    }
}
