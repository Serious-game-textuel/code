<?php

interface Interface_Condition {

    public function get_id();

    public function set_id();

    public function get_entite1();

    public function set_entite1();

    public function get_entite2();

    public function set_entite2();

    public function get_connecteur();

    public function set_connecteur();

    public function get_status();

    public function set_status();

    public function get_condition();

    public function set_condition();

    public function get_reaction_lieu();

    public function set_reaction_lieu();

    public function get_reaction_personnage();

    public function set_reaction_personnage();

    /**
     * 
     */
    public function do_reaction();

}

?>