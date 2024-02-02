<?php

require "./Interface_Materiel.php";

Class Inventaire {

    private $id;
    private $materiels;

    public function __construct($id, $materiels) {

    }

    public function get_id(){
        return null;
    }
    public function set_id($id){
        return null;
    }


    /**
     * @param int $name
     * @return Interface_Materiel
     */
    public function get_materiel($id){
        return null;
    }
    /**
     * @return Interface_Materiel[]
     */
    public function get_materiels(){
        return null;
    }

    public function ajout_materiel(){
        return null;
    }
    public function retrait_materiel(){
        return null;
    }

}

?>