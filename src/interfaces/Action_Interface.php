<?php

interface Action_Interface {

    /**
     * @return int
     */
    public function get_id();

    /**
    * @param int $id
    *
    * @return void
    */
    public function set_id(int $id);

    /**
    * @return Entity_Interface
    */
    public function get_entity1();

    /** 
    * @param Entity_Interface $entity1
    *
    * @return void
    */
    public function set_entity1(Entity_Interface $entity1);

    /**
    * @return Entity_Interface
    */
    public function get_entity2();

    /**
    * @param Entity_Interface $entity2
    *
    * @return void
    */
    public function set_entity2(Entity_Interface $entity2);

    /**
    * @return string
    */
    public function get_connector();

    /**
    * @param string $connector
    *
    * @return void
    */
    public function set_connector(string $connector);

    /**
    * @return Condition_Interface[]
    */
    public function get_conditions();

    /**
    * @param Condition_Interface[] $conditions
    *
    * @return void
    */
    public function set_conditions(array $conditions);

    /**
     * @return void
     */
    public function do_conditions();

}

?>