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
class Util {
    public static function check_array(array $array, string $class) {
        if (self::has_array_duplicate($array)) {
            throw new InvalidArgumentException('Invalid array : duplicate value.');
        }
        for ($i = 0; $i < count($array); $i++) {
            if (!isset($array[$i])) {
                throw new InvalidArgumentException('Invalid array : value at index '.$i.' is null.');
            }
            if ($class == 'string') {
                if (!is_string($array[$i])) {
                    throw new InvalidArgumentException('Invalid array : value at index '.$i.
                    ' is wrong class : '.gettype($array[$i]).' instead of '.$class.'.');
                }
            } else {
                if (!$array[$i] instanceof $class) {
                    throw new InvalidArgumentException('Invalid array : value at index '.$i.
                    ' is wrong class : '.gettype($array[$i]).' instead of '.$class.'.');
                }
            }
        }
    }

    public static function clean_array(array $array, string $class): array {
        $values = array_values($array);
        $result = [];
        if ($class == "string") {
            for ($i = 0; $i < count($values); $i++) {
                if (isset($values[$i]) && is_string($values[$i])) {
                    array_push($result, $values[$i]);
                }
            }
        } else {
            for ($i = 0; $i < count($values); $i++) {
                if (isset($values[$i]) && $values[$i] instanceof $class) {
                    array_push($result, $values[$i]);
                }
            }
        }
        for ($i = 0; $i < count($result); $i++) {
            for ($y = $i + 1; $y < count($result); $y++) {
                if (!is_subclass_of($class, 'Entity')) {
                    if ($result[$i] === $result[$y]) {
                        $result[$i] = null;
                    }
                } else {
                    if ($result[$i] === $result[$y] || $result[$i]->get_name() === $result[$y]->get_name()
                    || $result[$i]->get_id() === $result[$y]->get_id()) {
                        $result[$i] = null;
                    }
                }
            }
        }
        return array_values(array_filter($result));
    }

    public static function has_array_duplicate(array $array) {
        $clean = array_filter($array);
        for ($i = 0; $i < count($clean); $i++) {
            for ($y = $i + 1; $y < count($clean); $y++) {
                if (gettype($clean[$i]) == gettype($clean[$y]) && $clean[$i] == $clean[$y]) {
                    return true;
                }
            }
        }
        return false;
    }
}
