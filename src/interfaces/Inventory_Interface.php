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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Item_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Item.php');
interface Inventory_Interface {

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
     * @param int $id
     * @return Item_Interface
     */
    public function get_item(int $id);

    /**
     * @return array
     */
    public function get_items();

    /**
     * @param Item_Interface $item
     * @return void
     */
    public function add_item(Item_Interface $item);

    /**
     * @param Item_Interface $item
     * @return void
     */
    public function remove_item(Item_Interface $item);

    /**
     * @param Item_Interface $item
     * @return boolean
     */
    public function check_item(Item_Interface $item);
}
