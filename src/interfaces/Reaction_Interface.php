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
 * Interface Reaction_Interface
 * @package mod_stg
 * @copyright   2024 Paul Grandhomme, Loric Gallier, Benjamin Bracquier, Mathis Courant
 */
interface Reaction_Interface {

    /**
     * @return int
     */
    public function get_id();

    /**
     * @return string
     */
    public function get_description();

    /**
     * @param string
     * @return void
     */
    public function set_description(string $description);

    /**
     * @return array
     */
    public function get_old_status();

    /**
     * @param array
     * @return void
     */
    public function set_old_status(array $status);

    /**
     * @return array
     */
    public function get_new_status();

    /**
     * @param array
     * @return void
     */
    public function set_new_status(array $status);

    /**
     * @return array
     */
    public function get_old_item();

    /**
     * @param array
     * @return void
     */
    public function set_old_item(array $item);

    /**
     * @return array
     */
    public function get_new_item();

    /**
     * @param array
     * @return void
     */
    public function set_new_item(array $item);

    /**
     * @param int
     * @return Reaction_Interface
     */
    public static function get_instance(int $id);

    /**
     * @return string[]
     */
    public function do_reactions();

}
