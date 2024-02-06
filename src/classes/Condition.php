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

class Condition implements Condition_Interface {

    private int $id;
    private Entity_Interface $entity1;
    private Entity_Interface $entity2;
    private string $connector;
    private string $status;
    private Condition_Interface $condition;
    private array $reactions;

    public function __construct(int $id, Entity_Interface $entity1, Entity_Interface $entity2, string $connector,
     string $status, Condition_Interface $condition, array $reactions) {
        $this->id = $id;
        $this->entity1 = $entity1;
        $this->entity2 = $entity2;
        $this->connector = $connector;
        $this->status = $status;
        $this->condition = $condition;
        $this->reactions = $reactions;
    }

    public function get_id() {
        return $this->id;
    }

    public function set_id(int $id) {
        $this->id = $id;
    }

    public function get_entity1() {
        return $this->entity1;
    }

    public function set_entity1(Entity_Interface $entity1) {
        $this->entity1 = $entity1;
    }

    public function get_entity2() {
        return $this->entity2;
    }

    public function set_entity2(Entity_Interface $entity2) {
        $this->entity2 = $entity2;
    }

    public function get_connector() {
        return $this->connector;
    }

    public function set_connector(string $connector) {
        $this->connector = $connector;
    }

    public function get_status() {
        return $this->status;
    }

    public function set_status(string $status) {
        $this->status = $status;
    }

    public function get_condition() {
        return $this->condition;
    }

    public function set_condition(Condition_Interface $condition) {
        $this->condition = $condition;
    }

    public function get_reactions() {
        return $this->reactions;
    }

    public function set_reactions(array $reactions) {
        $this->reactions = $reactions;
    }

    public function do_reactions() {
    }
}