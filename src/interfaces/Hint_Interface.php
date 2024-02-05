<?php

interface Hint_Interface {

    /**
     * @return int
     */
    public function get_id();

    /**
     * @param int $id
     * @return void
     */
    public function set_id(int $id);

    /**
     * @return string
     */
    public function get_description();

    /**
     * @param string $description
     * @return void
     */
    public function set_description(string $description);
}

?>