<?php

interface Interface_Action {


    public function get_id();

    public function set_id();

    public function get_entite1();

    public function set_entite1();

    public function get_entite2();

    public function set_entite2();

    public function get_connecteur();

    public function set_connecteur();

    public function get_condition();

    public function set_condition();

    /**
     * @return boolean
     */
    public function check_condition();

}

?>