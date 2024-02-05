<?php

class App implements App_Interface {

    private Game_Interface $game;
    private Game_Interface $save;

    public function __construct(Game_Interface $game, Game_Interface $save) {
        $this->game = $game;
        $this->save = $save;
        
    }
 
    public function get_game() {
        return $this->game;
    }

    public function set_game(Game_Interface $game) {
        $this->game = $game;
    }

    public function get_save() {
        return $this->save;
    }

    public function set_save(Game_Interface $save) {
        $this->save = $save;
    }

}

?>