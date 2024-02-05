<?php

require "./Inventory_Interface.php";
require "./Character_Interface.php";
require "./Action_Interface.php";
require "./Hint_Interface.php";

interface Location_Interface extends Entity_Interface {

    /**
     * @return Inventory_Interface
     */
    public function get_inventory();

    /**
     * @return Character_Interface[]
     */
    public function get_characters();

    /**
     * @param Action_Interface[]
     */
    public function get_actions();
    
    /**
     * @return Hint_Interface[]
     */
    public function get_hints();


}

?>
