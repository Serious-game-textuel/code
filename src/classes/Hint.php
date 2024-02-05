<?php

class Hint implements Hint_Interface {
    
    private int $id;
    private string $description;

    public function __construct(int $id, string $description) {
        $this->id = $id;
        $this->description = $description;
        
    }

    public function get_id() {
        return $this->id;
    }

    public function set_id(int $id) {
        $this->id = $id;
    }

    public function get_description() {
        return $this->description;
    }

    public function set_description(string $description) {
        $this->description = $description;
    }

}

?>