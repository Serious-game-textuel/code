<?php

interface Entity_Interface {

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

    /**
     * @return string
     */
    public function get_name();

    /**
     * @param string $name
     * @return void
     */
    public function set_name(string $name);

    /**
     * @return string
     */
    public function get_status();

    /**
     * @param string $status
     * @return void
     */
    public function set_status(string $status);


}

?>