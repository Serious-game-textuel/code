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

/**
 * Interface Character_Interface
 * @package mod_serioustextualgame
 */
interface Character_Interface extends Entity_Interface {

    /**
     * @return Inventory_Interface
     */
    public function get_inventory();

    /**
     * @param Item_Interface $item
     * @return boolean
     */
    public function has_item_character(Item_Interface $item);
}
