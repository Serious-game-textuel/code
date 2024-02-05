<?php

class Action implements Action_Interface {

    private int $id;
    private Entity_Interface $entity1;
    private Entity_Interface $entity2;
    private string $connector;
    private array $conditions;

    public function __construct(int $id ,Entity_Interface $entity1, Entity_Interface $entity2, string $connector, array $conditions) {
        $this->id = $id;
        $this->entity1 = $entity1;
        $this->entity2 = $entity2;
        $this->connector = $connector;
        $this->conditions = $conditions;
    }
 
    public function get_id() {
        return $this->id;
        
    }

    public function set_id(int $id) {
        $this->id = $id;
    }

    public function get_entity1() {
        return $this->entity1;
    }

    public function set_entity1(Entity_Interface $entity1) {
        $this->entity1 = $entity1;
    }

    public function get_entity2() {
        return $this->entity2;
    }

    public function set_entity2(Entity_Interface $entity2) {
        $this->entity2 = $entity2;
    }

    public function get_connector() {
        return $this->connector;
    }

    public function set_connector(string $connector) {
        $this->connector = $connector;
    }

    public function get_conditions() {
        return $this->conditions;
    }

    public function set_conditions(array $conditions) {
        $this->conditions = $conditions;
    }

    public function do_conditions() {
        
    }

}

?>