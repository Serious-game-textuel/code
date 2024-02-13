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
class Inventory implements Inventory_Interface {

    private int $id;
    private array $items;

    public function __construct(array $items) {
        $this->id = Id_Class::generate_id(self::class);
        Util::check_array($items, Item_Interface::class);
        $this->items = $items;
    }

    public function get_id() {
        return $this->id;
    }

    public function get_item(int $id) {
        return $this->items[$id] ?? null;
    }
    public function get_items() {
        return $this->items;
    }

    public function add_item(array $item) {
        array_merge($this->items, Util::clean_array($item, Item_Interface::class));
    }
    public function remove_item(array $item) {
        $item = Util::clean_array($item, Item_Interface::class);
        $key = array_search($item, $this->items);
        if ($key !== false) {
            unset($this->items[$key]);
        }
    }

    public function check_item(Item_Interface $item) {
        foreach ($this->items as $itemarray) {
            if (in_array($item, $itemarray, true)) {
                return true;
            }
        }
        return false;
    }

}
