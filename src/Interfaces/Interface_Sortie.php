<?php

interface Interface_Sortie {


    public function get_id();

    public function set_id();

    public function get_nom();

    public function set_nom();

    /**
     * @return boolean
     */
    public function check_condition();

}

?>