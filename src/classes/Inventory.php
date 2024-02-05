<?php

class Inventory implements Inventory_Interface {

    private int $id;
    private array $items;

    public function __construct(int $id, array $items) {
        $this->id = $id;
        $this->items = $items;
        
    }

    public function get_id() {
        return $this->id;
    }

    public function set_id(int $id) {
        $this->id = $id;
    }

    public function get_item(int $id) {
        return $this->items[$id] ?? null;
    }
    
    public function get_items() {
        return $this->items;
    }

    public function add_item(Item_Interface $item) {
        $this->items[] = $item;
    }
    
    public function remove_item(Item_Interface $item) {
        $key = array_search($item, $this->items);
        if ($key !== false) {
            unset($this->items[$key]);
        }
    }

}

?>