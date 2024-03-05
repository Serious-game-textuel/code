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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Reaction.php');
class Character_Reaction extends Reaction {

    private int $id;

    public function __construct(?int $id, string $description, array $oldstatus, array $newstatus,
    array $olditem, array $newitem, ?Character_Interface $character, ?Location_Interface $newlocation) {
        global $DB;
        if (!isset($id)) {
            $super = new reaction(null, $description, $oldstatus, $newstatus, $olditem, $newitem);
            parent::__construct($super->get_id(), "", [], [], [], []);
            $this->id = $DB->insert_record('characterreaction', [
                'reaction' => $super->get_id(),
                'character' => $character->get_id(),
                'newlocation' => $newlocation->get_id(),
            ]);
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {characterreaction} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Character_Reaction object of ID:".$id." exists.");
            }
            $sql = "select reaction from {characterreaction} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
            $super = $DB->get_field_sql($sql, ['id' => $id]);
            parent::__construct($super, "", [], [], [], []);
            $this->id = $id;
        }
    }

    public static function get_instance(int $id) {
        return new Character_Reaction($id, "", [], [], [], [], null, null);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_character() {
        global $DB;
        $sql = "select character from {characterreaction} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return Character::get_instance($DB->get_field_sql($sql, ['id' => $this->get_id()]));
    }

    public function get_new_location() {
        global $DB;
        $sql = "select newlocation from {characterreaction} where ". $DB->sql_compare_text('id')
        . " = ".$DB->sql_compare_text(':id');
        return Location::get_instance($DB->get_field_sql($sql, ['id' => $this->get_id()]));
    }

    public function set_new_location(Location_Interface $newlocation) {
        global $DB;
        $DB->set_field('characterreaction', 'newlocation', $newlocation->get_id(), ['id' => $this->get_id()]);
    }

}
