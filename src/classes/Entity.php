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

abstract class Entity implements Entity_Interface {

    private int $id;

    private string $description;

    private string $name;

    private string $status;
    public function __construct(int $id, string $description, string $name, string $status) {
        $this->id = $id;
        $this->description = $description;
        $this->name = $name;
        $this->status = $status;
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

    public function get_name() {
        return $this->name;
    }

    public function set_name(string $name) {
        $this->name = $name;
    }

    public function get_status() {
        return $this->status;
    }

    public function set_status(string $status) {
        $this->status = $status;
    }

}