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

class Condition implements Condition_Interface {

    private int $id;
    private array $reactions;

    public function __construct(int $id, array $reactions){
        $this->id = $id;
        $this->reactions = $reactions;

    }

    public function get_id() {
        return $this->id;
    }

    public function set_id(int $id) {
        $this->id = $id;
    }

    public function get_reactions() {
        return $this->reactions;
    }

    public function set_reactions(array $reactions) {
        $this->reactions = $reactions;
    }

    public function do_reactions() {
    }
}
