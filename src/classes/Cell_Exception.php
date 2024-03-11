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

/**
 * Class Cell_Exception
 * @package mod_serioustextualgame
 */

class Cell_Exception extends Exception {

    public function __construct($message, $row, $col) {
        if (App::get_instance()->get_language() == Language::FR) {
            parent::__construct($message . " (ligne : " . ($row + 1) . ", colonne : " . ($col + 1) . ")", 0, null);
        } else {
            parent::__construct($message . " (row: " . ($row + 1) . ", column: " . ($col + 1) . ")", 0, null);
        }
        
    }

}
