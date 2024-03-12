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

/**
 * Interface App_Interface
 * @package mod_stg
 */
interface App_Interface {
     /**
      * @return string
      */
    public function get_language();

    /**
     * Returns the number of deaths.
     * @return int
     */
    public function get_deaths();

    /**
     * Increases the number of deaths.
     */
    public function add_deaths();

    /**
     * Returns the number of actions performed.
     * @return int
     */
    public function get_actions();

    /**
     * Increases the number of actions performed.
     * @return void
     */
    public function add_action();

    /**
     * @return DateTime
     */
    public function get_start_time();

    /**
     * @param DateTime $time
     */
    public function set_start_time(DateTime $time);

    /**
     * @return Default_Action_Interface
     */
    public function get_default_action_search();

    /**
     * @return Default_Action_Interface
     */
    public function get_default_action_interact();

    /**
     * Returns the list of all the visited locations
     * @return string[]
     */
    public function get_visited_locations();

    /**
     * @param Location_Interface $location
     */
    public function add_visited_location(Location_Interface $location);

    /**
     * @return int
     */
    public function get_id();
    /**
     * @return Game_Interface
     */
    public function get_game();

    /**
     * @param Game_Interface $game
     *
     * @return void
     */
    public function set_game(Game_Interface $game);

    /**
     * @param string $entityname
     *
     * @return ?Entity_Interface
     */
    public function get_startentity($entityname);

    /**
     * @param Entity_Interface $entity
     *
     * @return void
     */
    public function add_startentity(Entity_Interface $entity);

    /**
     * @param int
     */
    public function add_startentity_from_id(int $entityid);

    /**
     * @return App_Interface|null
     */
    public static function get_instance();

    /**
     * @return void
     */
    public function restart_game_from_start();

     /**
      * @return void
      */
    public function restart_game_from_save();

     /**
      * @return App_Interface|null
      */
    public function get_save();
    public function set_current_location(Location_Interface $location);
    public function get_current_location();
    public function do_action(string $actionname, bool $isdefault);
    public function get_player();

}

