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
        if ($class == 'string') {
            if (Util::has_array_duplicate($array)) {
                throw new InvalidArgumentException('Invalid array : duplicate value.');
            }
            for ($i=0; $i<sizeof($array); $i++) {
                if ($array[$i] == null) {
                    throw new InvalidArgumentException('Invalid array : value at index '.$i.' is null.');
                }
                if (!is_string($array[$i])) {
                    throw new InvalidArgumentException('Invalid array : value at index '.$i.
                    ' is wrong class : '.gettype($array[$i]).' instead of '.$class.'.');
                }
            }
        } else {
            if (Util::has_array_duplicate_name_or_id($array)) {
                throw new InvalidArgumentException('Invalid array : duplicate value (due to id or name).');
            }
            for ($i=0; $i<sizeof($array); $i++) {
                if ($array[$i] == null) {
                    throw new InvalidArgumentException('Invalid array : value at index '.$i.' is null.');
                }
                if (!$array[$i] instanceof $class) {
                    throw new InvalidArgumentException('Invalid array : value at index '.$i.
                    ' is wrong class : '.gettype($array[$i]).' instead of '.$class.'.');
                }
            }
        }
    }

    public static function clean_array(array $array, string $class) {
        $result = [];
        if ($class == "string") {
            for ($i=0; $i<sizeof($array); $i++) {
                if ($array[$i] != null && is_string($array[$i])) {
                    array_push($result, $array[$i]);
                }
            }
        } else {
            for ($i=0; $i<sizeof($array); $i++) {
                if ($array[$i] != null && $array[$i] instanceof $class) {
                    array_push($result, $array[$i]);
                }
            }
        }
        for ($i=0; $i<sizeof($result); $i++) {
            for ($y= $i+1; $y<sizeof($result); $y++) {
                if ($class == 'string') {
                    if ($result[$i] === $result[$y]) {
                        $result[$i]=null;
                    }
                } else {
                    if ($result[$i] === $result[$y] || $result[$i]->get_name() === $result[$y]->get_name()
                    || $result[$i]->get_id() === $result[$y]->get_id()) {
                        $result[$i]=null;
                    }
                }
            }
        }
        return array_filter($result);
    }

    public static function has_array_duplicate(array $array) {
        $clean = array_filter($array);
        for ($i= 0; $i<sizeof($clean); $i++) {
            for ($y= $i+1; $y<sizeof($clean); $y++) {
                if ($clean[$i] == $clean[$y]) {
                    return true;
                }
            }
        }
        return false;
    }
    public static function has_array_duplicate_name_or_id(array $array) {
        $clean = array_filter($array);
        for ($i= 0; $i<sizeof($clean); $i++) {
            for ($y= $i+1; $y<sizeof($clean); $y++) {
                if ($clean[$i] === $clean[$y] || $clean[$i]->get_name() === $clean[$y]->get_name()
                || $clean[$i]->get_id() === $clean[$y]->get_id()) {
                    return true;
                }
            }
        }
        return false;
    }
}
