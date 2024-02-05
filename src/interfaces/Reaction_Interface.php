<?php

interface Reaction_Interface {

    public function get_id();

    public function set_id();

    public function get_description();

    public function set_description();

    public function get_old_status();

    public function set_old_status();

    public function get_new_status();

    public function set_new_status();

    public function get_old_item();

    public function set_old_item();

    public function get_new_item();

    public function set_new_item();

}

?>