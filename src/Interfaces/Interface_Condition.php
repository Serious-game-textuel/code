<?php
require(__DIR__.'/../../../../config.php');

interface Interface_Condition {
    public function get_id();
    public function set_id();
    public function get_description();
    public function set_description();

    /**
     * @return void
     */
    public function do_reaction();

}
