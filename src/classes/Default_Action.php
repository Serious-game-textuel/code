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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Default_Action_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Action.php');

class Default_Action extends Action implements Default_Action_Interface {

    private int $id;

    public function __construct(?int $id, string $description, array $conditions) {
        global $DB;
        if (!isset($id)) {
            parent::__construct(null, $description, $conditions);
            $this->id = $DB->insert_record('defaultaction', [
                'action_id' => parent::get_id(),
            ]);
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {defaultaction} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Default_action object of ID:".$id." exists.");
            }
            $sql = "select action_id from {defaultaction} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
            $super = $DB->get_field_sql($sql, ['id' => $id]);
            parent::__construct($super, "", []);
            $this->id = $id;
        }
    }

    public function get_parent_id() {
        return parent::get_id();
    }

    public static function get_instance_from_parent_id(int $actionid) {
        global $DB;
        $sql = "select id from {defaultaction} where "
        . $DB->sql_compare_text('action_id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $actionid]);
        return Default_Action::get_instance($id);
    }

    public static function get_instance(int $id) {
        return new Default_Action($id, "", []);
    }

    public function do_conditions_verb(string $verb) {
        $game = App::get_instance()->get_game();
        $game->add_action();
        $tokendescription = explode('"', $this->get_description());
        $result = [];
        foreach ($tokendescription as $token) {
            if (str_replace(' ', '', $token) == '+verbe+' ||
            str_replace(' ', '', $token) == 'verbe+' ||
            str_replace(' ', '', $token) == '+verbe' ||
            str_replace(' ', '', $token) == 'verbe') {
                array_push($result, $verb);
            } else {
                array_push($result, $token);
            }
        }
        return [implode("", $result)];
    }

    public function do_conditions() {
        return $this->do_conditions_verb('');
    }

    public function get_id() {
        return $this->id;
    }
}
