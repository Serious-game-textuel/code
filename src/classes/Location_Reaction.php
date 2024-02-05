<?php

class Location_Reaction extends Reaction {

    private Location_Interface $location; // Location affected by the reaction

    public function __construct(int $id, string $description, string $old_status, string $new_status, Item_Interface $old_item, Item_Interface $new_item, Location_Interface $location) {
        parent::__construct($id, $description, $old_status, $new_status, $old_item, $new_item);
        $this->location = $location;
    }

    public function get_location() {
        return $this->location;
    }

    public function set_location(Location_Interface $location) {
        $this->location = $location;
    }

}

?>
