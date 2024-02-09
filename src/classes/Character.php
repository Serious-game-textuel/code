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
class Character extends Entity implements Character_Interface {

    private Inventory_Interface $inventory;

    public function __construct(string $description, string $name, array $status, Inventory_Interface $inventory) {
        parent::__construct($description, $name, $status);
        $this->inventory = $inventory;
    }
    public function get_inventory() {
        return $this->inventory;
    }

    public function has_item_character(Item_Interface $item) {
        if ($this->inventory !== null) {
            return $this->inventory->check_item($item);
        }
        return false;
    }

}
