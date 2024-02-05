<?php

class Item extends Entity implements Item_Interface {

    public function __construct(int $id, string $description, string $name, string $status) {
        parent::__construct($id, $description, $name, $status);
    }

}

?>