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

class Location_Reaction extends Reaction {

    private Location_Interface $location;

    public function __construct(int $id, string $description, array $oldstatus,
     array $newstatus, array $olditem, array $newitem, Location_Interface $location) {
        parent::__construct($id, $description, $oldstatus, $newstatus, $olditem, $newitem);
        $this->location = $location;
    }
    public function get_location() {
        return $this->location;
    }

    public function set_location(Location_Interface $location) {
        $this->location = $location;
    }

}

