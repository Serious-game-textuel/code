<?php

class Id_Class {
    
    private static $map = array();

    public static function generate_id($class) {
        if (array_key_exists($class, self::$map)) {
            self::$map[$class]++;
        }
        else {
            self::$map[$class] = 1;
        }
        return self::$map[$class];
    }

}

echo "Hello";

echo Id_Class::generate_id(Item::class);
echo Id_Class::generate_id(Item::class);
echo Id_Class::generate_id(Item::class);
echo Id_Class::generate_id(Inventory::class);
echo Id_Class::generate_id(Inventory::class);
echo Id_Class::generate_id(Item::class);