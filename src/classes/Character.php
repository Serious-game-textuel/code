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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Character_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Inventory.php');
class Character extends Entity implements Character_Interface {

    private int $id;

    public function __construct(?int $id, string $description, string $name,
    array $status, array $items, ?Location_Interface $currentlocation) {
        if (!isset($id)) {
            $super = new Entity(null, $description, $name, $status);
            parent::__construct($super->get_id(), "", "", []);
            $inventory = new Inventory(null, $items);
            global $DB;
            $this->id = $DB->insert_record('character', [
                'entity' => $super->get_id(),
                'inventory' => $inventory->get_id(),
                'currentlocation' => $currentlocation->get_id(),
            ]);
        } else {
            $this->id = $id;
        }
    }

    public static function get_instance(int $id) {
        return new Character($id, "", "", [], [], null);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_inventory() {
        global $DB;
        $sql = "select inventory from {character} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return Inventory::get_instance($DB->get_field_sql($sql, ['id' => $this->get_id()]));
    }

    public function has_item_character(Item_Interface $item) {
        $inventory = $this->get_inventory();
        if ($inventory !== null) {
            return $inventory->check_item($item);
        }
        return false;
    }

    public function get_current_location() {
        global $DB;
        $sql = "select currentlocation from {character} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return Location::get_instance($DB->get_field_sql($sql, ['id' => $this->get_id()]));
    }

    public function set_currentlocation(Location_Interface $newlocation) {
        global $DB;
        $DB->set_field('character', 'currentlocation', $newlocation->get_id(), ['id' => $this->get_id()]);
    }

}
