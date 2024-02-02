<?php

require "./Interface_Inventaire.php";
require "./interface_Personnage.php";
require "./Interface_Action.php";
require "./Interface_Indice.php";

interface Interface_Lieu {
    public function get_id();
    public function get_description();
    public function get_nom();
    public function get_status();
    public function set_id($id);
    public function set_description($description);
    public function set_nom($nom);
    public function set_status($status);


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
