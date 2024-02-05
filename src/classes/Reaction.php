<?php

abstract class Reaction implements Reaction_Interface {

    private int $id;
    private string $description;
    private string $old_status;
    private string $new_status;
    private Item_Interface $old_item;
    private Item_Interface $new_item;

    public function __construct(int $id, string $description, string $old_status, string $new_status, Item_Interface $old_item, Item_Interface $new_item) {
        $this->id = $id;
        $this->description = $description;
        $this->old_status = $old_status;
        $this->new_status = $new_status;
        $this->old_item = $old_item;
        $this->new_item = $new_item;
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

    public function get_old_status() {
        return $this->old_status;
    }

    public function set_old_status(string $status) {
        $this->old_status = $status;
    }

    public function get_new_status() {
        return $this->new_status;
    }

    public function set_new_status(string $status) {
        $this->new_status = $status;
    }

    public function get_old_item() {
        return $this->old_item;
    }

    public function set_old_item(Item_Interface $item) {
        $this->old_item = $item;
    }

    public function get_new_item() {
        return $this->new_item;
    }

    public function set_new_item(Item_Interface $item) {
        $this->new_item = $item;
    }


}

?>
