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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Id_Class.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Util.php');
abstract class Entity implements Entity_Interface {

    private int $id;

    private string $description;

    private string $name;

    private array $status;
    public function __construct(string $description, string $name, array $status) {
        $app = App::get_instance();
        if ($app->get_startentity($name) != null) {
            throw new InvalidArgumentException("Each entity name must be unique : ".$name);
        }
        $this->id = Id_Class::generate_id(self::class);
        $this->description = $description;
        $this->name = $name;
        Util::check_array($status, 'string');
        $this->status = $status;
        $app->add_startentity($this);
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
        $this->status = Util::clean_array($status, 'string');
        return [];
    }

    public function add_status(array $status) {
        $this->status = Util::clean_array(array_merge($this->status, $status), 'string');
    }

    public function remove_status(array $status) {
        $this->status = Util::clean_array(array_diff($this->status, $status), 'string');
    }
}
