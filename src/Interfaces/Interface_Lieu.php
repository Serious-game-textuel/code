<?php

require "./Interface_Inventaire.php";
require "./interface_Personnage.php";
require "./Interface_Action.php";
require "./Interface_Indice.php";

interface Interface_Lieu extends Interface_Entite {

    /**
     * @return Interface_Inventaire
     */
    public function get_inventaire();

    /**
     * @return Interface_Personnage[]
     */
    public function get_personnages();

    /**
     * @param Interface_Action $action
     * @return bool
     */
    public function check_action( $action );
    
    /**
     * @return Interface_Indice[]
     */
    public function get_indices();


}

?>
