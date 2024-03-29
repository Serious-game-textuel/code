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
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Item_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Item.php');

/**
 * Interface Inventory_Interface
 * @package mod_stg
 * @copyright   2024 Paul Grandhomme, Loric Gallier, Benjamin Bracquier, Mathis Courant
 */
interface Inventory_Interface {

    /**
     * @return int
     */
    public function get_id();

    /**
     * @param int $id
     * @return Item_Interface|null
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

    /**
     * @param int
     * @return Inventory_Interface
     */
    public static function get_instance(int $id);

    /**
     * @return string
     */
    public function __toString();
}
