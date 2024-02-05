<?php

require "../Language.php";

interface Game_Interface {

    /**
     * @return int
     */
    public function get_id();

    /**
     * @param int $id
     * @return void
     */
    public function set_id(int $id);

    /**
     * Returns the number of deaths.
     * @return int
     */
    public function get_deaths();

    /**
     * @param int $deaths
     * @return void
     */
    public function set_deaths(int $deaths);

    /**
     * Returns the list of all the actions previously performed.
     * @return Action_Interface[]
     */
    public function get_actions();

    /**
     * Add $action to the list of actions.
     * @param Action_Interface $action
     * @return void
     */
    public function add_action(Action_Interface $action);

    /**
     * Returns the list of all the visited locations 
     * @return Location_Interface[]
     */
    public function get_visited_locations();

    /**
     * @param Location_Interface $location
     * @return void
     */
    public function add_visited_location(Location_Interface $location);

    /**
     * @return DateTime
     */
    public function get_start_time();

    /**
     * @param DateTime $time
     * @return void
     */
    public function set_start_time(DateTime $time);

    /**
     * @return Language
     */
    public function get_language();

    /**
     * @param Language $language
     * @return void
     */
    public function set_language(Language $language);

    /**
     * @return Location_Interface
     */
    public function get_current_location();

    /**
     * @param Location_Interface
     * @return void
     */
    public function set_current_location(Location_Interface $location);

}

?>
