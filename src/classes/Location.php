<?php

class Location extends Entity implements Location_Interface {

    private Inventory_Interface $inventory;
    private array $characters;
    private array $hints;
    private array $actions;

    public function __construct(int $id, string $description, string $name, string $status, Inventory_Interface $inventory, array $characters, array $hints, array $actions) {
        parent::__construct($id, $description, $name, $status);
        $this->inventory = $inventory;
        $this->characters = $characters;
        $this->hints = $hints;
        $this->actions = $actions;
    }

    public function get_inventory() {
        return $this->inventory;
    }

    public function get_characters() {
        return $this->characters;
    }

    public function get_actions() {
        return $this->actions;
    }
    
    public function get_hints() {
        return $this->hints;
    }


}

?>
