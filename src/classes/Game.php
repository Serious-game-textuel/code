<?php

class Game implements Game_Interface {

    private int $id;
    private int $deaths;
    private array $actions;
    private array $visited_locations;
    private DateTime $start_time;
    private Language $language;
    private Location_Interface $current_location;

    public function __construct(int $id, int $deaths, array $actions, array $visited_locations, DateTime $start_time, Language $language, Location_Interface $current_location) {
        $this->id = $id;
        $this->deaths = $deaths;
        $this->actions = $actions;
        $this->visited_locations = $visited_locations;
        $this->start_time = $start_time;
        $this->language = $language;
        $this->current_location = $current_location;
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
        return $this->visited_locations;
    }
    public function set_visited_locations(array $visited_locations) {
        $this->visited_locations = $visited_locations;
    }

    public function add_visited_location(Location_Interface $location) {
        $this->visited_locations[] = $location;
    }

    public function get_start_time() {
        return $this->start_time;
    }
    public function set_start_time(DateTime $start_time) {
        $this->start_time = $start_time;
    }

    public function get_language() {
        return $this->language;
    }
    public function set_language(Language $language) {
        $this->language = $language;
    }

    public function get_current_location() {
        return $this->current_location;
    }
    public function set_current_location(Location_Interface $current_location) {
        $this->current_location = $current_location;
    }

}

?>