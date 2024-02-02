<?php

require "./Interface_Materiel.php";

interface Interface_Inventaire {

    public function get_id();
    public function set_id($id);


    /**
     * @param int $name
     * @return Interface_Materiel
     */
    public function get_materiel($id);
    /**
     * @return Interface_Materiel[]
     */
    public function get_materiels();

}

?>