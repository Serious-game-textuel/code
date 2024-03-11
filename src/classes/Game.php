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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Game_Interface.php');

class Game implements Game_Interface {

    private int $id;
    private array $visitedlocations;
    private Player_Character $player;
    private array $entities = [];

    public function __construct(array $visitedlocations, array $entities) {
        $this->id = Id_Class::generate_id(self::class);
        Util::check_array($visitedlocations, Location_Interface::class);
        $this->visitedlocations = $visitedlocations;
        $this->entities = $entities;
        foreach ($this->entities as $e) {
            if ($e instanceof Player_Character) {
                $this->player = $e;
            }
        }
    }

    public function get_id() {
        return $this->id;
    }

    public function get_player() {
        return $this->player;
    }

    public function get_visited_locations() {
        return $this->visitedlocations;
    }
    public function set_visited_locations(array $visitedlocations) {
        $this->visitedlocations = Util::clean_array($visitedlocations, Location_Interface::class);
    }

    public function add_visited_location(Location_Interface $location) {
        array_push($this->visitedlocations, $location);
        $this->visitedlocations = Util::clean_array($this->visitedlocations, Location_Interface::class);
    }

    public function get_current_location() {
        return $this->player->get_current_location();
    }
    public function set_current_location(Location_Interface $currentlocation) {
        $this->player->set_currentlocation($currentlocation);
    }

    public function get_entities() {
        return $this->entities;
    }

    public function set_entities(array $entities) {
        $this->entities = Util::clean_array($entities, Entity_Interface::class);
    }

    public function add_entity(Entity_Interface $entity) {
        array_push($this->entities, $entity);
        $this->entities = Util::clean_array($this->entities, Entity_Interface::class);
    }

    /**
     * @return ?Entity_Interface
     */
    public function get_entity(string $name) {
        foreach ($this->entities as $entity) {
            if ($entity->get_name() == $name) {
                return $entity;
            }
        }
        return null;
    }

}
