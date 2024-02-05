<?php

require "./Item_Interface.php";

interface Inventory_Interface {

    public function get_id();

    public function set_id($id);

    /**
     * @param int $id
     * @return Item_Interface
     */
    public function get_item($id);

    /**
     * @return Item_Interface[]
     */
    public function get_items();

    public function add_item();
    
    public function remove_item();

}

?>