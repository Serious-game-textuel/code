<?php

interface Interface_Reaction_Personnage {

    public function get_id();

    public function set_id();

    public function get_description();

    public function set_description();

    public function get_personnage_affecte();

    public function set_personnage_affecte();

    public function get_ancien_statut();

    public function set_ancien_statut();

    public function get_nouveau_statut();

    public function set_nouveau_statut();

    public function get_ajout_materiel();

    public function set_ajout_materiel();

    public function get_retrait_materiel();

    public function set_retrait_materiel();

    public function get_deplacement();

    public function set_deplacement();

}

?>