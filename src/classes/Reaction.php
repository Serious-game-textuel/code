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

abstract class Reaction implements Reaction_Interface {

    private int $id;
    private string $description;
    private string $oldstatus;
    private string $newstatus;
    private Item_Interface $olditem;
    private Item_Interface $newitem;

    public function __construct(int $id, string $description, string $oldstatus, string $newstatus,
     Item_Interface $olditem, Item_Interface $newitem) {
        $this->id = $id;
        $this->description = $description;
        $this->oldstatus = $oldstatus;
        $this->newstatus = $newstatus;
        $this->olditem = $olditem;
        $this->newitem = $newitem;
    }

    public function get_id() {
        return $this->id;
    }

    public function set_id(int $id) {
        $this->id = $id;
    }

    public function get_description() {
        return $this->description;
    }

    public function set_description(string $description) {
        $this->description = $description;
    }

    public function get_old_status() {
        return $this->oldstatus;
    }

    public function set_old_status(string $status) {
        $this->oldstatus = $status;
    }

    public function get_new_status() {
        return $this->newstatus;
    }

    public function set_new_status(string $status) {
        $this->newstatus = $status;
    }

    public function get_old_item() {
        return $this->olditem;
    }

    public function set_old_item(Item_Interface $item) {
        $this->olditem = $item;
    }

    public function get_new_item() {
        return $this->newitem;
    }

    public function set_new_item(Item_Interface $item) {
        $this->newitem = $item;
    }


}
