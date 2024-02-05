<?php

interface Action_Interface {

    public function get_id();

    public function set_id();

    public function get_entity1();

    public function set_entity1();

    public function get_entity2();

    public function set_entity2();

    public function get_connector();

    public function set_connector();

    public function get_conditions();

    public function set_conditions();

    /**
     * @return boolean
     */
    public function check_conditions();

}

?>