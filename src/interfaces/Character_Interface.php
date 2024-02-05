<?php

require "./Inventory_Interface.php";

interface Character_Interface extends Entity_Interface {

    /**
     * @return Inventory_Interface
     */
    public function get_inventory();
    
}
?>