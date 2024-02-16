<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

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

    public function get_startentity($entityname);

    public function get_all_startentities();

    public function add_startentity(Entity_Interface $entity);

    public static function get_instance();

    public function restart_game_from_save();

    public function restart_game_from_start();

    public function create_save();

}

