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
 * Interface Item_Interface
 * @package mod_serioustextualgame
 */
interface Item_Interface extends Entity_Interface {
    /**
     * @return Item_Interface
     */
    public function get_inventory();

    /**
     * @param int
     * @return Item_Interface
     */
    public static function get_instance(int $id);

    /**
     * @return int
     */
    public function get_id();

    /**
     * @return int
     */
    public function get_parent_id();

    /**
     * @param int
     * @return Item_Interface
     */
    public static function get_instance_from_parent_id(int $entityid);
}
