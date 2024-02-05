<?php

require "./Item_Interface.php";

interface Inventory_Interface {

    /**
     * @return int
     */
    public function get_id();

    /**
     * @param int $id
     * @return void
     */
    public function set_id(int $id);

    /**
     * @param int $id
     * @return Item_Interface
     */
    public function get_item(int $id);

    /**
     * @return Item_Interface[]
     */
    public function get_items();

    /**
     * @param Item_Interface $item
     * @return void
     */
    public function add_item(Item_Interface $item);
    
    /**
     * @param Item_Interface $item
     * @return void
     */
    public function remove_item(Item_Interface $item);

}

?>