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
    private int $deaths;
    private int $actions;
    private array $visitedlocations;
    private ?DateTime $starttime;
    private ?Player_Character $player;
    private ?Default_Action_Interface $defaultactionsearch;
    private ?Default_Action_Interface $defaultactioninteract;
    private array $entities = [];
    private static array $instances = [];

    public function __construct(int $deaths, int $actions, array $visitedlocations, ?DateTime $starttime, ?Player_Character $player,
    ?Default_Action_Interface $defaultactionsearch, ?Default_Action_Interface $defaultactioninteract, array $entities) {
        $this->id = Id_Class::generate_id(self::class);
        $this->deaths = $deaths;
        $this->actions = $actions;
        Util::check_array($visitedlocations, Location_Interface::class);
        $this->visitedlocations = $visitedlocations;
        $this->starttime = $starttime;
        $this->player = $player;
        $this->defaultactionsearch = $defaultactionsearch;
        $this->defaultactioninteract = $defaultactioninteract;
        $this->entities = $entities;
        self::$instances[$this->id] = $this;
    }

    public static function get_instance_by_id(int $id): ?self {
        return self::$instances[$id] ?? null;
    }

    public function get_id() {
        return $this->id;
    }

    public function get_deaths() {
        return $this->deaths;
    }
    public function add_deaths() {
        $this->deaths ++;
    }

    public function get_actions() {
        return $this->actions;
    }
    public function set_actions(int $actions) {
        $this->actions = $actions;
    }

    public function add_action() {
        $this->actions ++;
    }

    public function get_player() {
        return $this->player;
    }

    public function set_player(Player_Character $player) {
        $this->player = $player;
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

    public function get_start_time() {
        return $this->starttime;
    }
    public function set_start_time(DateTime $starttime) {
        $this->starttime = $starttime;
    }

    public function get_current_location() {
        return $this->player->get_current_location();
    }
    public function set_current_location(Location_Interface $currentlocation) {
        $this->player->set_currentlocation($currentlocation);
    }

    public function get_default_action_search() {
        return $this->defaultactionsearch;
    }

    public function set_default_action_search(Default_Action_Interface $action) {
        $this->defaultactionsearch = $action;
    }

    public function get_default_action_interact() {
        return $this->defaultactioninteract;
    }

    public function set_default_action_interact(Default_Action_Interface $action) {
        $this->defaultactioninteract = $action;
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
