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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Reaction_Interface.php');
abstract class Reaction implements Reaction_Interface {

    private int $id;
    private string $description;
    private array $oldstatus;
    private array $newstatus;
    private array $olditem;
    private array $newitem;

    public function __construct(string $description, array $oldstatus, array $newstatus,
    array $olditem, array $newitem) {
        $this->id = Id_Class::generate_id(self::class);
        for ($i=0; $i<sizeof($oldstatus); $i++) {
            if (!is_string($oldstatus[$i])) {
                $oldstatus[$i] = null;
            }
        }
        for ($i=0; $i<sizeof($newstatus); $i++) {
            if (!is_string($newstatus[$i])) {
                $newstatus[$i] = null;
            }
        }
        for ($i=0; $i<sizeof($olditem); $i++) {
            if (!$olditem[$i] instanceof Item_Interface) {
                $olditem[$i] = null;
            }
        }
        for ($i=0; $i<sizeof($newitem); $i++) {
            if (!$newitem[$i] instanceof Item_Interface) {
                $newitem[$i] = null;
            }
        }
        $this->description = $description;
        $this->oldstatus = array_filter($oldstatus);
        $this->newstatus = array_filter($newstatus);
        $this->olditem = array_filter($olditem);
        $this->newitem = array_filter($newitem);
    }

    public function get_id() {
        return $this->id;
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

    public function set_old_status(array $status) {
        for ($i=0; $i<sizeof($status); $i++) {
            if (!is_string($status[$i])) {
                $status[$i] = null;
            }
        }
        $this->oldstatus = array_filter($status);
    }

    public function get_new_status() {
        return $this->newstatus;
    }

    public function set_new_status(array $status) {
        for ($i=0; $i<sizeof($status); $i++) {
            if (!is_string($status[$i])) {
                $status[$i] = null;
            }
        }
        $this->newstatus = array_filter($status);
    }

    public function get_old_item() {
        return $this->olditem;
    }

    public function set_old_item(array $item) {
        for ($i=0; $i<sizeof($item); $i++) {
            if (!$item[$i] instanceof Item_Interface) {
                $item[$i] = null;
            }
        }
        $this->olditem = array_filter($item);
    }

    public function get_new_item() {
        return $this->newitem;
    }

    public function set_new_item(array $item) {
        for ($i=0; $i<sizeof($item); $i++) {
            if (!$item[$i] instanceof Item_Interface) {
                $item[$i] = null;
            }
        }
        $this->newitem = array_filter($item);
    }


}
