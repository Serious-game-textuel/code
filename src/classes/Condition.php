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

class Condition implements Condition_Interface {

    private int $id;

    public function __construct(?int $id, array $reactions) {
        global $DB;
        if (!isset($id)) {
            Util::check_array($reactions, Reaction_Interface::class);
            $this->id = $DB->insert_record('condition', ['test' => 'a']);
            foreach ($reactions as $reaction) {
                $DB->insert_record('condition_reactions', [
                    'condition_id' => $this->id,
                    'reaction_id' => $reaction->get_id(),
                ]);
            }
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {condition} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Condition object of ID:".$id." exists.");
            }
            $this->id = $id;
        }
    }

    public static function get_instance(int $id) {
        return new Condition($id, []);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_reactions() {
        $reactions = [];
        global $DB;
        $sql = "select reaction_id from {condition_reactions} where "
        . $DB->sql_compare_text('condition_id') . " = ".$DB->sql_compare_text(':id');
        $ids = $DB->get_fieldset_sql($sql, ['id' => $this->get_id()]);
        foreach ($ids as $id) {
            array_push($reactions, Reaction::get_instance($id));
        }
        return $reactions;
    }

    public function set_reactions(array $reactions) {
        $reactions = Util::clean_array($reactions, Reaction_Interface::class);
        global $DB;
        $DB->delete_records('condition_reactions', ['condition_id' => $this->get_id()]);
        foreach ($reactions as $reaction) {
            $DB->insert_record('condition_reactions', [
                'condition_id' => $this->id,
                'reaction_id' => $reaction->get_id(),
            ]);
        }
    }

    public function do_reactions() {
        $reactions = $this->get_reactions();
        $descriptions = [];
        foreach ($reactions as $reaction) {
            array_merge($descriptions, $reaction->do_reactions());
            array_push($descriptions, $reaction->get_description());
        }
        if (empty($descriptions)) {
            return "pas de réaction";
        }
        return implode(' / ', $descriptions);
    }

    public function is_true() {
        try {
            $nodecondition = Node_Condition::get_instance_from_parent_id($this->get_id());
            return $nodecondition->is_true();
        } catch (Exception $e) {
            try {
                $leafcondition = Leaf_Condition::get_instance_from_parent_id($this->get_id());
                return $leafcondition->is_true();
            } catch (Exception $e) {}
        }
        return false;
    }

}
