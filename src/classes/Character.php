<?php

class Character extends Entity implements Character_Interface {

    private Inventory_Interface $inventory;

    public function __construct(int $id, string $description, string $name, string $status, Inventory_Interface $inventory) {
        parent::__construct($id, $description, $name, $status);
        $this->inventory = $inventory;
    }
    
    public function get_inventory() {
        return $this->inventory;
    }

}

?>