<?php

interface Condition_Interface {

    public function get_id();

    public function set_id();

    public function get_entity1();

    public function set_entity1();

    public function get_entity2();

    public function set_entity2();

    public function get_connector();

    public function set_connector();

    public function get_status();

    public function set_status();

    public function get_condition();

    public function set_condition();

    public function get_reactions();

    public function set_reactions();

    /**
     * 
     */
    public function do_reactions();

}

?>