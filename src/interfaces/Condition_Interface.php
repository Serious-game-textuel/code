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

interface Condition_Interface {

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
     * @return Entity_Interface
     */
    public function get_entity1();

    /**
     * @param Entity_Interface $entity1
     * @return void
     */
    public function set_entity1(Entity_Interface $entity1);

    /**
     * @return Entity_Interface
     */
    public function get_entity2();

    /**
     * @param Entity_Interface $entity2
     * @return void
     */
    public function set_entity2(Entity_Interface $entity2);

    /**
     * @return string
     */
    public function get_connector();

    /**
     * @param string $connector
     * @return void
     */
    public function set_connector(string $connector);


    /**
     * @return string
     */
    public function get_status();

    /**
     * @param string $status
     * @return void
     */
    public function set_status(string $status);

    /**
     * @return Condition_Interface
     */
    public function get_condition();

    /**
     * @param Condition_Interface $condition
     * @return void
     */
    public function set_condition(Condition_Interface $condition);

    /**
     * @return Reaction_Interface[]
     */
    public function get_reactions();

    /**
     * @param Reaction_Interface[] $reactions
     * @return void
     */
    public function set_reactions(array $reactions);

    /**
     * @return void
     */
    public function do_reactions();

}
