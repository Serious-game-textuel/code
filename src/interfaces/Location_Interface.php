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

interface Location_Interface extends Entity_Interface {

    /**
     * @return Inventory_Interface
     */
    public function get_inventory();

    /**
     * @return Character_Interface[]
     */
    public function get_characters();

    /**
     * @param Action_Interface[]
     */
    public function get_actions();

    /**
     * @return Hint_Interface[]
     */
    public function get_hints();
    /**
     * @param Action_Interface $action
     * @return bool
     * // This method checks if the actions are valid for the location and called do_condition
     */
    public function check_actions( Action_Interface $action);


}
