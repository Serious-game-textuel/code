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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Entity.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Character_Interface.php');
class Character extends Entity implements Character_Interface {

    private Inventory_Interface $inventory;

    private ?Location_Interface $currentlocation;

    public function __construct(string $description, string $name,
    array $status, array $items, ?Location_Interface $currentlocation) {
        parent::__construct($description, $name, $status);
        $this->inventory = new Inventory($items);
        $this->currentlocation = $currentlocation;
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

    public function get_current_location() {
        return $this->currentlocation;
    }

    public function set_currentlocation(Location_Interface $newlocation) {
        $this->currentlocation = $newlocation;
    }

}
