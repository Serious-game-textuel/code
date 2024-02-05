<?php

interface Condition_Interface {

    /**
    * @return int
    */
    public function get_id();

    /** 
    * @param int
    * @return void
    */
    public function set_id(int $id);

    /**
    * @return Entity_Interface
    */
    public function get_entity1();

    /** 
    * @param Entity_Interface $entity1
    * @return void
    */
    public function set_entity1(Entity_Interface $entity1);

    /** 
    * @return Entity_Interface
    */
    public function get_entity2();

    /**
    * @param Entity_Interface $entity2
    * @return void
    */
    public function set_entity2(Entity_Interface $entity2);

    /**
    * @return string
    */
    public function get_connector();

    /**
    * @param string $connector
    * @return void
    */
    public function set_connector(string $connector);


    /**
    * @return string
    */
    public function get_status();

    /**
    * @param string $status
    * @return void
    */
    public function set_status(string $status);

    /**
    * @return Condition_Interface
    */
    public function get_condition();

    /**
    * @param Condition_Interface $condition
    * @return void
    */
    public function set_condition(Condition_Interface $condition);

    /**
    * @return Reaction_Interface[]
    */
    public function get_reactions();

    /**
    * @param Reaction_Interface[] $reactions
    * @return void
    */
    public function set_reactions(array $reactions);

    /**
     * @return void
     */
    public function do_reactions();

}

?>