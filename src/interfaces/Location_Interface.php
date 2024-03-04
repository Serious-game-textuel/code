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

interface Location_Interface extends Entity_Interface {

    /**
     * @return Inventory_Interface
     */
    public function get_inventory();

    /**
     * @return Action_Interface[]
     */
    public function get_actions();

    /**
     * @param Action_Interface[] $actions
     *
     * @return void
     */
    public function set_actions(array $actions);

    /**
     * @return Hint_Interface[]
     */
    public function get_hints();

    /**
     * @param string $action
     *
     * @return string[]
     * // This method checks if the actions are valid for the location by parsing the string into a Action and called do_condition
     */
    public function check_actions(string $action);

    /**
     * @param string $action
     * @return ?Action_Interface
     */
    public function is_action_valide(string $action);

    /**
     * @param Item_Interface $item
     * @return bool
     */
    public function has_item_location(Item_Interface $item);

    /**
     * @param int
     * @return Location_Interface
     */
    public static function get_instance(int $id);

}
