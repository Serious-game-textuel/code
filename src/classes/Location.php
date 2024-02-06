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

class Location extends Entity implements Location_Interface {

    private Inventory_Interface $inventory;
    private array $characters;
    private array $hints;
    private array $actions;

    public function __construct(int $id, string $description, string $name, array $status,
     Inventory_Interface $inventory, array $characters, array $hints, array $actions) {
        parent::__construct($id, $description, $name, $status);
        $this->inventory = $inventory;
        $this->characters = $characters;
        $this->hints = $hints;
        $this->actions = $actions;
    }
    public function get_inventory() {
        return $this->inventory;
    }

    public function get_characters() {
        return $this->characters;
    }

    public function get_actions() {
        return $this->actions;
    }
    public function get_hints() {
        return $this->hints;
    }

    public function check_actions(string $action) {
        $action = explode(" ", $action);
        $connector = $action[0];
        $entity1 = $action[1];
        $entity2 = $action[2];
        for ($i = 0; $i < count($this->actions); $i++) {
            if ($this->actions[$i]->get_entity1()->get_name() == $entity1
                && $this->actions[$i]->get_entity2()->get_name() == $entity2
                && $this->actions[$i]->get_connector() == $connector) {
                $this->actions[$i]->do_condition();
                return true;
            }
        }
        return false;
    }
    public function has_item_location(Item_Interface $item) {
        return $this->inventory->check_item($item);
    }
}
