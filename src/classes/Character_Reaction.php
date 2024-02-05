<?php

class Character_Reaction extends Reaction {

    private Character_Interface  $character;
    private Location_Interface $new_location;

    public function __construct( int $id, string $description, string $old_status, string $new_status, Item_Interface $old_item, Item_Interface $new_item,Character_Interface $character, Location_Interface $new_location) {
        parent::__construct($id, $description, $old_status, $new_status, $old_item, $new_item);
        $this->character = $character;
        $this->new_location = $new_location;

    }

    public function get_character() {
        return $this->character;
    }

    public function set_character(Character_Interface $character) {
        $this->character = $character;
    }

    public function get_new_location() {    
        return $this->new_location;
    }

    public function set_new_location(Location_Interface $new_location) {
        $this->new_location = $new_location;
    }

}

?>
