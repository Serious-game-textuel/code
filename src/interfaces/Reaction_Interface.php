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

interface Reaction_Interface {

    /**
     * @return int
     */
    public function get_id();

    /**
     * @param int
     * @return void
     */
    public function set_id(int $id);

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
     * @return string
     */
    public function get_old_status();

    /**
     * @param string
     * @return void
     */
    public function set_old_status(string $status);

    /**
     * @return string
     */
    public function get_new_status();

    /**
     * @param string
     * @return void
     */
    public function set_new_status(string $status);

    /**
     * @return Item_Interface
     */
    public function get_old_item();

    /**
     * @param Item_Interface
     * @return void
     */
    public function set_old_item(Item_Interface $item);

    /**
     * @return Item_Interface
     */
    public function get_new_item();

    /**
     * @param Item_Interface
     * @return void
     */
    public function set_new_item(Item_Interface $item);

}
