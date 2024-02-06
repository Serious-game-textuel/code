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

class Game implements Game_Interface {

    private int $id;
    private int $deaths;
    private array $actions;
    private array $visitedlocations;
    private DateTime $starttime;
    private Language $language;
    private Location_Interface $currentlocation;

    public function __construct(int $id, int $deaths, array $actions, array $visitedlocations, DateTime $starttime,
     Language $language, Location_Interface $currentlocation) {
        $this->id = $id;
        $this->deaths = $deaths;
        $this->actions = $actions;
        $this->visitedlocations = $visitedlocations;
        $this->starttime = $starttime;
        $this->language = $language;
        $this->currentlocation = $currentlocation;
    }

    public function get_id() {
        return $this->id;
    }
    public function set_id(int $id) {
        $this->id = $id;
    }

    public function get_deaths() {
        return $this->deaths;
    }
    public function set_deaths(int $deaths) {
        $this->deaths = $deaths;
    }

    public function get_actions() {
        return $this->actions;
    }
    public function set_actions(array $actions) {
        $this->actions = $actions;
    }

    public function add_action(Action_Interface $action) {
        $this->actions[] = $action;
    }


    public function get_visited_locations() {
        return $this->visitedlocations;
    }
    public function set_visited_locations(array $visitedlocations) {
        $this->visitedlocations = $visitedlocations;
    }

    public function add_visited_location(Location_Interface $location) {
        $this->visitedlocations[] = $location;
    }

    public function get_start_time() {
        return $this->starttime;
    }
    public function set_start_time(DateTime $starttime) {
        $this->starttime = $starttime;
    }

    public function get_language() {
        return $this->language;
    }
    public function set_language(Language $language) {
        $this->language = $language;
    }

    public function get_current_location() {
        return $this->currentlocation;
    }
    public function set_current_location(Location_Interface $currentlocation) {
        $this->currentlocation = $currentlocation;
    }

}