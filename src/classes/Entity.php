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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Entity_Interface.php');
abstract class Entity implements Entity_Interface {

    private int $id;

    private string $description;

    private string $name;

    private array $status;
    public function __construct(string $description, string $name, array $status) {
        $game = Game::getinstance();
        if ($game->get_entity($name) !== null) {
            throw new InvalidArgumentException("Chaque entité doit avoir un nom unique : ".$name);
        }
        $this->id = Id_Class::generate_id(self::class);
        $this->description = $description;
        $this->name = $name;
        for ($i=0; $i<sizeof($status); $i++) {
            if (!is_string($status[$i])) {
                $status[$i] = null;
            }
        }
        $this->status = array_filter($status);
        $game->add_entity($this);
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

    public function get_name() {
        return $this->name;
    }

    public function set_name(string $name) {
        $this->name = $name;
    }

    public function get_status() {
        return $this->status;
    }

    public function set_status(array $status) {
        for ($i=0; $i<sizeof($status); $i++) {
            if (!is_string($status[$i])) {
                $status[$i] = null;
            }
        }
        $this->status = array_filter($status);
    }

    public function add_status(array $status) {
        for ($i=0; $i<sizeof($status); $i++) {
            if (!is_string($status[$i])) {
                $status[$i] = null;
            }
        }
        $this->status = array_merge($this->status, array_filter($status));
    }

    public function remove_status(array $status) {
        for ($i=0; $i<sizeof($status); $i++) {
            if (!is_string($status[$i])) {
                $status[$i] = null;
            }
        }
        $this->status = array_diff($this->status, array_filter($status));
    }

}
