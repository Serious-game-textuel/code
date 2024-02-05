<?php

interface App_Interface {
    /**
    * @return Game_Interface
    */
    public function get_game();

    /**
    * @param Game_Interface $game
    *
    * @return void
    */
    public function set_game(Game_Interface $game);

    /**
    * @return Game_Interface
    */
    public function get_save();

    /**
    * @param Game_Interface $save
    *
    * @return void
    */
    public function set_save(Game_Interface $save);

}

?>
