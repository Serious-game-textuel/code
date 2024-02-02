<?php
require(__DIR__.'/../../../../config.php');
require(__DIR__."../Langue.php");

interface Interface_Partie {
    public function get_id();
    public function get_action();
    public function get_lieu_visite();
    public function get_heure_debut();
    public function get_langue();
    public function set_id($id);
    public function set_action($action);
    public function set_lieu_visite($lieuVisite);
    public function set_heure_debut($heureDebut);
    public function set_langue($langue);

    /**
     * @param int $name
     * @return boolean
     */
    public function change_lieu($id);
}

