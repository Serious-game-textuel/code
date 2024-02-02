<?php

require "../Langue.php";

interface Interface_Partie {

    public function get_id();
    public function set_id($id);

    public function get_morts();
    public function set_morts();

    public function get_actions();
    public function set_actions();

    public function get_lieux_visites();
    public function set_lieux_visites();

    public function get_heure_debut();
    public function set_heure_debut();

    public function get_langue();
    public function set_langue();

    public function get_lieu_actuel();
    public function set_lieu_actuel();

}

?>
