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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Hint_Interface.php');
class Hint implements Hint_Interface {
    private int $id;

    public function __construct(?int $id, string $description) {
        if (!isset($id)) {
            global $DB;
            $this->id = $DB->insert_record('hint', [
                'description' => $description,
            ]);
        } else {
            $this->id = $id;
        }
    }

    public static function get_instance(int $id) {
        return new Hint($id, "");
    }

    public function get_id() {
        return $this->id;
    }

    public function get_description() {
        global $DB;
        $sql = "select description from {hint} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->get_id()]);
    }

    public function set_description(string $description) {
        global $DB;
        $DB->set_field('hint', 'description', $description, ['id' => $this->get_id()]);
    }

}
