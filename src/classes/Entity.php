<?php

abstract class Entity implements Entity_Interface {

    private int $id;

    private string $description;

    private string $name;

    private string $status;
    
    public function __construct(int $id, string $description, string $name, string $status) {
        $this->id = $id;
        $this->description = $description;
        $this->name = $name;
        $this->status = $status;
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

    public function get_name() {
        return $this->name;
        
    }

    public function set_name(string $name) {
        $this->name = $name;
    }

    public function get_status() {
        return $this->status;
    }

    public function set_status(string $status) {
        $this->status = $status;
    }

}

?>