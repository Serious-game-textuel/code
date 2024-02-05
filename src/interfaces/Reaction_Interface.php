<?php

interface Reaction_Interface {

    /**
     * @return int
     */
    public function get_id();

    /**
     * @param int
     * @return void
     */
    public function set_id(int $id);

    /**
     * @return string
     */
    public function get_description();

    /**
     * @param string
     * @return void
     */
    public function set_description(string $description);

    /**
     * @return string
     */
    public function get_old_status();

    /**
     * @param string
     * @return void
     */
    public function set_old_status(string $status);

    /**
     * @return string
     */
    public function get_new_status();

    /**
     * @param string
     * @return void
     */
    public function set_new_status(string $status);

    /**
     * @return Item_Interface
     */
    public function get_old_item();

    /**
     * @param Item_Interface
     * @return void
     */
    public function set_old_item(Item_Interface $item);

    /**
     * @return Item_Interface
     */
    public function get_new_item();

    /**
     * @param Item_Interface
     * @return void
     */
    public function set_new_item(Item_Interface $item);

}

?>