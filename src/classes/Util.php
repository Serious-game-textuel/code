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

class Util {
    public static function check_array(array $array, string $class) {
        for ($i=0; $i<sizeof($array); $i++) {
            if ($array[$i] == null) {
                throw new InvalidArgumentException('Invalid array : value at index '.$i.' is null.');
            }
            if (get_class($array[$i]) != $class) {
                throw new InvalidArgumentException('Invalid array : value at index '.$i.
                ' is wrong class : '.get_class($array[$i]).' istead of '.$class.'.');
            }
        }
    }

    public static function clean_array(array $array, string $class) {
        $result = [];
        for ($i=0; $i<sizeof($array); $i++) {
            if ($array[$i] != null && get_class($array[$i]) == $class) {
                array_push($result, $array[$i]);
            }
        }
        return array_filter($result);
    }
}
