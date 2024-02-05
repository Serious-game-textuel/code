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

defined('MOODLE_INTERNAL') || die();
require(__DIR__."../Langue.php");

interface Interface_Partie {
    public function get_id();
    public function get_action();
    public function get_lieu_visite();
    public function get_heure_debut();
    public function get_langue();
    public function set_id($id);
    public function set_action($action);
    public function set_lieu_visite($lieuvisite);
    public function set_heure_debut($heuredebut);
    public function set_langue($langue);

    /**
     * @param int $name
     * @return boolean
     */
    public function change_lieu($id);
}

