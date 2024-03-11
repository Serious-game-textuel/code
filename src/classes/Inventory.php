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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Inventory_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Item.php');

class Inventory implements Inventory_Interface {

    private int $id;

    public function __construct(?int $id, array $items) {
        global $DB;
        if (!isset($id)) {
            Util::check_array($items, Item_Interface::class);
            $this->id = $DB->insert_record('inventory', ['test' => 'n']);
            foreach ($items as $item) {
                $DB->insert_record('inventory_items', [
                    'inventory_id' => $this->id,
                    'item_id' => $item->get_id(),
                ]);
            }
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {inventory} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Inventory object of ID:".$id." exists.");
            }
            $this->id = $id;
        }
    }

    public static function get_instance(int $id) {
        return new Inventory($id, []);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_item(int $id) {
        global $DB;
        $present = $DB->record_exists_sql(
            "SELECT id FROM {inventory_items} WHERE ".$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
            ['id' => $id]
        );
        if ($present) {
            return Item::get_instance($id);
        } else {
            return null;
        }
    }
    public function get_items() {
        $items = [];
        global $DB;
        $sql = "select item_id from {inventory_items} where "
        . $DB->sql_compare_text('inventory_id') . " = ".$DB->sql_compare_text(':id');
        $ids = $DB->get_fieldset_sql($sql, ['id' => $this->id]);
        foreach ($ids as $id) {
            array_push($items, Item::get_instance($id));
        }
        return $items;
    }

    public function add_item(Item_Interface $item) {
        $items = $this->get_items();
        array_push($items, $item);
        $items = Util::clean_array($items, Item_Interface::class);
        global $DB;
        $DB->delete_records('inventory_items', ['inventory_id' => $this->id]);
        foreach ($items as $item) {
            $DB->insert_record('inventory_items', [
                'inventory_id' => $this->id,
                'item_id' => $item->get_id(),
            ]);
        }
    }

    public function remove_item(Item_Interface $item) {
        global $DB;
        $DB->delete_records('inventory_items', ['inventory_id' => $this->id, 'item_id' => $item->get_id()]);
    }

    public function check_item(Item_Interface $item) {
        foreach ($this->get_items() as $i) {
            if ($i->get_id() == $item->get_id()) {
                return true;
            }
        }
        return false;
    }

    public function __toString() {
        $items = $this->get_items();
        $description = "Inventaire : ";
        if (count($items) == 0) {
            $description .= "vide";
        } else {
            foreach ($items as $item) {
                $description .= $item->get_name().", ";
            }
        }
        return rtrim($description, " ,");
    }

}
