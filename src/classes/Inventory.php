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

class Inventory implements Inventory_Interface {

    private int $id;
    private array $items;

    public function __construct(int $id, array $items) {
        $this->id = $id;
        $this->items = $items;
    }

    public function get_id() {
        return $this->id;
    }

    public function set_id(int $id) {
        $this->id = $id;
    }

    public function get_item(int $id) {
        return $this->items[$id] ?? null;
    }
    public function get_items() {
        return $this->items;
    }

    public function add_item(Item_Interface $item) {
        $this->items[] = $item;
    }
    public function remove_item(Item_Interface $item) {
        $key = array_search($item, $this->items);
        if ($key !== false) {
            unset($this->items[$key]);
        }
    }

}