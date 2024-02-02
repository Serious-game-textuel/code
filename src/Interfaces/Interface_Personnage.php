<?php
require(__DIR__.'/../../../../config.php');
require(__DIR__."./Interface_Inventaire.php");

interface Interface_Personnage {
    public function get_id();
    public function get_description();
    public function get_nom();
    public function get_status();
    public function set_id( $id );
    public function set_description( $description );
    public function set_nom( $nom );
    public function set_status( $status );

    /**
     * @return Interface_Inventaire
     */
    public function get_inventaire();
}
