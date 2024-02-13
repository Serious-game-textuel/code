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

class Character_Reaction extends Reaction {

    private Character_Interface  $character;
    private ?Location_Interface $newlocation;

    public function __construct(string $description, array $oldstatus, array $newstatus,
    array $olditem, array $newitem, Character_Interface $character, ?Location_Interface $newlocation) {
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
        parent::__construct($description, array_filter($oldstatus), array_filter($newstatus),
        array_filter($olditem), array_filter($newitem));
        $this->character = $character;
        $this->newlocation = $newlocation;

    }

    public function get_character() {
        return $this->character;
    }

    public function set_character(Character_Interface $character) {
        $this->character = $character;
    }

    public function get_new_location() {
        return $this->newlocation;
    }

    public function set_new_location(Location_Interface $newlocation) {
        $this->newlocation = $newlocation;
    }

}
