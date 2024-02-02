<?php

require "./Interface_Inventaire.php";
require "./interface_Personnage.php";
require "./Interface_Action.php";
require "./Interface_Indice.php";

Class Lieu extends Entite {

    private $inventaire;
    private $personnages;
    private $indices;
    private $actions;

    public function __construct($inventaire, $personnages, $indices, $actions) {

    }

    /**
     * @return Interface_Inventaire
     */
    public function get_inventaire(){
        return null;
    }

    /**
     * @return Interface_Personnage[]
     */
    public function get_personnages(){
        return null;
    }

    /**
     * @param Interface_Action $action
     * @return bool
     */
    public function check_action( $action ){
        return null;
    }
    
    /**
     * @return Interface_Indice[]
     */
    public function get_indices(){
        return null;
    }


}

?>
