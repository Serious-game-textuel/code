<?php

require "../Language.php";

interface Game_Interface {

    public function get_id();
    public function set_id($id);

    public function get_deaths();
    public function set_deaths();

    public function get_actions();
    public function set_actions();

    public function get_visited_locations();
    public function set_visited_locations();

    public function get_start_time();
    public function set_start_time();

    public function get_language();
    public function set_language();

    public function get_current_location();
    public function set_current_location();

}

?>
