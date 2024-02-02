<?php
require(__DIR__.'/../../../../config.php');

interface Interface_Sauvegarde {
    public function get_id();
    public function set_id($id);

    /**
     * @return void
     */
    public function creer_sauvegarde();
    /**
     * @return void
     */
    public function charger_sauvegarde();
}
