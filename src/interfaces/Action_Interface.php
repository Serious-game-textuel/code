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

interface Action_Interface {

    /**
     * @return int
     */
    public function get_id();

    /**
     * @param int $id
     *
     * @return void
     */
    public function set_id(int $id);

    /**
     * @return string
     */
    public function get_description();

    /**
     * @param string $description
     *
     * @return void
     */
    public function set_description(string $description);

    /**
     * @return Condition_Interface[]
     */
    public function get_conditions();

    /**
     * @param Condition_Interface[] $conditions
     *
     * @return void
     */
    public function set_conditions(array $conditions);

    /**
     * @return array
     */
    public function do_conditions();

}
