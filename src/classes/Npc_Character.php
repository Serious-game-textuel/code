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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Character.php');
class Npc_Character extends Character {

    private int $id;

    public function __construct(?int $id, string $description, string $name,
    array $status, array $items, ?Location_Interface $currentlocation) {
        global $DB;
        if (!isset($id)) {
            $super = new Character(null, $description, $name, $status, $items, $currentlocation);
            parent::__construct($super->get_id(), "", "", [], [], null);
            $this->id = $DB->insert_record('npccharacter', [
                'character' => $super->get_id(),
            ]);
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {npccharacter} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Npc_Character object of ID:".$id." exists.");
            }
            $sql = "select character from {npccharacter} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
            $super = $DB->get_field_sql($sql, ['id' => $id]);
            parent::__construct($super, "", "", [], [], null);
            $this->id = $id;
        }
    }

    public static function get_instance(int $id) {
        return new Npc_Character($id, "", "", [], [], null);
    }

    public function get_id() {
        return $this->id;
    }
}
