<?php

require "./Interface_Inventaire.php";

interface Interface_Personnage extends Interface_Entite {

    /**
     * @return Interface_Inventaire
     */
    public function get_inventaire();
    
}
?>