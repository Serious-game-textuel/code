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

    private Character_Interface $character;
    private ?Location_Interface $newlocation;

    public function __construct(string $description, array $oldstatus, array $newstatus,
    array $olditem, array $newitem, Character_Interface $character, ?Location_Interface $newlocation) {
        Util::check_array($oldstatus, 'string');
        Util::check_array($newstatus, 'string');
        Util::check_array($olditem, Item_Interface::class);
        Util::check_array($newitem, Item_Interface::class);
        parent::__construct($description, $oldstatus, $newstatus, $olditem, $newitem);
        $this->character = $character;
        $this->newlocation = $newlocation;

    }

    public function get_character() {
        return $this->character;
    }

    public function get_new_location() {
        return $this->newlocation;
    }

    public function set_new_location(Location_Interface $newlocation) {
        $this->newlocation = $newlocation;
    }

}
