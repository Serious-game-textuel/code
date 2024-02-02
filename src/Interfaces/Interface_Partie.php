<?php

require "../Langue.php";

interface Partie {
    /**
     * @param int $name
     * @return boolean
     */
    public function change_lieu($id);

    /**
     * @param Langue $langue
     * @return void
     */
    public function set_langue($langue);
}

?>