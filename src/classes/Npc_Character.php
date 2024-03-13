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
require_once($CFG->dirroot . '/mod/stg/src/classes/Character.php');

/**
 * Class Npc_Character
 * @package mod_stg
 * @copyright   2024 Paul Grandhomme, Loric Gallier, Benjamin Bracquier, Mathis Courant
 */
class Npc_Character extends Character {

    private int $id;

    public function __construct(?int $id, string $description, string $name,
    array $status, array $items, ?Location_Interface $currentlocation) {
        global $DB;
        if (!isset($id)) {
            parent::__construct(null, $description, $name, $status, $items, $currentlocation);
            $this->id = $DB->insert_record('stg_npccharacter', [
                'character_id' => parent::get_id(),
            ]);
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {stg_npccharacter} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Npc_Character object of ID:".$id." exists.");
            }
            $sql = "select character_id from {stg_npccharacter} where "
            . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
            $super = $DB->get_field_sql($sql, ['id' => $id]);
            parent::__construct($super, "", "", [], [], null);
            $this->id = $id;
        }
    }

    public function get_parent_id() {
        return parent::get_id();
    }

    public static function get_instance_from_parent_id(int $characterid): Npc_Character {
        global $DB;
        $sql = "select id from {stg_npccharacter} where "
        . $DB->sql_compare_text('character_id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $characterid]);
        return self::get_instance($id);
    }

    public static function get_instance(int $id): Npc_Character {
        return new Npc_Character($id, "", "", [], [], null);
    }

    public function get_id() {
        return $this->id;
    }
}
