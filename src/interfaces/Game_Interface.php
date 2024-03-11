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

interface Game_Interface {

    /**
     * @return int
     */
    public function get_id();

    /**
     * @return Player_Character
     */
    public function get_player();

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
     * @return Location_Interface
     */
    public function get_current_location();

    /**
     * @param Location_Interface
     * @return void
     */
    public function set_current_location(Location_Interface $location);

    /**
     * @return Default_Action_Interface
     */
    public function get_default_action_search();

    /**
     * @param Default_Action_Interface
     * @return void
     */
    public function set_default_action_search(Default_Action_Interface $action);

    /**
     * @return Default_Action_Interface
     */
    public function get_default_action_interact();

    /**
     * @param Default_Action_Interface
     * @return void
     */
    public function set_default_action_interact(Default_Action_Interface $action);

    /**
     * @return array
     */
    public function get_entities();

    /**
     * @param array
     * @return void
     */
    public function set_entities(array $entities);

    /**
     * @return void
     */
    public function add_entity(Entity_Interface $entity);

    /**
     * @return ?Entity_Interface
     */
    public function get_entity(string $name);

}
