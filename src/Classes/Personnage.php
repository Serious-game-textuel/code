<?php

require "./Interface_Inventaire.php";

class Personnage extends Entite {

    private $inventaire;
    
    public function __construct($inventaire) {

    }

    /**
     * @return Interface_Inventaire
     */
    public function get_inventaire() {
        return null;
    }
    
}
?>