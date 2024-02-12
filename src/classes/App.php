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

class App implements App_Interface {

    private Game_Interface $game;

    private Game_Interface $save;

    private array $csvdata;

    private static App_Interface $instance = null;

    private array $startentities;

    private string $playerkeyword;

    private Language $language;

    public function __construct($csvdata, Language $language) {
        self::$instance = $this;
        $this->csvdata = $csvdata;
        $this->language = $language;
        $this->startentities = [];
        if ($this->language == Language::FR) {
            $this->playerkeyword = "joueur";
        } else {
            $this->playerkeyword = "player";
        }

        $this->parse();
    }

    public static function get_instance() {
        return self::$instance;
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

    public function get_startentity($entityname) {
        foreach ($this->startentities as $e) {
            if ($e->get_name() == $entityname) {
                return $e;
            }
        }
        return null;
    }

    public function add_startentity(Entity_Interface $entity) {
        array_push($this->startentities, $entity);
    }

    private function parse() {

        $this->create_items();
        $this->create_characters();
        $this->create_locations();
        $this->create_all_actions();
        
    }

    private function create_items() {
        $col = 1;
        $lign = 0;
        $foundline = false;
        $items = [];
        while (!$foundline && sizeof($this->csvdata)>$lign) {
            if (strcmp($this->csvdata[$lign][0],"OBJETS") != 0) {
                $foundline = true;
            } else {
                $lign = $lign + 1;
            }
        }
        if (!$foundline) {
            throw new Exception("OBJETS line not found");
        }
        while ($this->csvdata[$lign][$col] != null) {
            $name = $this->get_cell_string(3, $col);
            $description = $this->get_cell_string(4, $col);
            $statuses = $this->get_cell_array_string(5, $col);
            new Item($description, $name, $statuses);
            $col++;
        }
    }

    private function create_characters() {
        $col = 1;
        $foundline = false;
        $lign = 0;
        while (!$foundline && sizeof($this->csvdata)>$lign) {
            if (strcmp($this->csvdata[$lign][0],"PERSONNAGES") != 0) {
                $foundline = true;
            } else {
                $lign = $lign + 1;
            }
        }
        if (!$foundline) {
            throw new Exception("PERSONNAGES line not found");
        }
        while ($this->csvdata[$lign][$col] != null) {
            $name = $this->get_cell_string(8, $col);
            $description = $this->get_cell_string(9, $col);
            $statuses = $this->get_cell_array_string(10, $col);
            $itemnames = $this->get_cell_array_string(11, $col);
            $items = [];
            foreach ($itemnames as $itemname) {
                $item = $this->get_startentity($itemname);
                if ($item == null || !($item instanceof Item)) {
                    throw new Exception($itemname . "is not an item");
                }
                array_push($items, $item);
            }
            if ($name == $this->playerkeyword) {
                new Player_Character($description, $name, $statuses, $items, null);
            } else {
                new Npc_Character($description, $name, $statuses, $items, null);
            }
            $col++;
        }
    }

    private function create_locations() {
        $col = 1;
        $foundline = false;
        $lign = 0;
        while (!$foundline && sizeof($this->csvdata)>$lign) {
            if (strcmp($this->csvdata[$lign][0],"LIEUX") != 0) {
                $foundline = true;
            } else {
                $lign = $lign + 1;
            }
        }
        if (!$foundline) {
            throw new Exception("LIEUX line not found");
        }
        while ($this->csvdata[$lign][$col] != null) {
            $name = $this->get_cell_string(14, $col);
            $statuses = $this->get_cell_array_string(15, $col);
            $itemnames = $this->get_cell_array_string(16, $col);
            $items = [];
            foreach ($itemnames as $itemname) {
                $item = $this->get_startentity($itemname);
                if ($item == null || !($item instanceof Item)) {
                    throw new Exception($itemname . "is not an item");
                }
                array_push($items, $item);
            }
            $hints = explode('/', $this->csvdata[18][$col]);
            new Location($name, $statuses, $items, $hints, []);
            $col++;
        }
        $col = 1;
        while ($this->csvdata[14][$col] != null) {
            $locationname = $this->get_cell_string(14, $col);
            $location = $this->get_startentity($locationname);
            if ($location == null || !($location instanceof Location)) {
                throw new Exception($locationname . "is not a location");
            }
            $character = $this->get_startentity($name);
            $characternames = $this->get_cell_array_string(17, $col);
            foreach ($characternames as $name) {
                $character = $this->get_startentity($name);
                if ($character == null || !($character instanceof Character)) {
                    throw new Exception($name . "is not a character");
                }
                $character->set_currentlocation($location);
            }
        }
    }

    private function create_all_actions() {
        $col = 1;
        while ($this->csvdata[14][$col] != null) {
            $locationname = $this->get_cell_string(14, $col);
            $location = $this->get_startentity($locationname);
            if ($location == null || !($location instanceof Location)) {
                throw new Exception($locationname . "is not a location");
            }
            $this->create_column_actions($location, $col);
            $col++;
        }
    }

    private function create_column_actions($location, $col) {
        $rowstep = 10;
        $row = 20;
        $actions = [];
        while ($this->csvdata[$row][$col] != null) {
            $description = $this->get_cell_string($row + 1, $col);
            $condition = $this->get_cell_string($row + 2, $col);
            if () {

            }
            $row += $rowstep;
        }
    }

    private function get_cell_string($row, $column) {
        return self::tokenize($this->csvdata[$row][$column]);
    }

    private function get_cell_array_string($row, $column) {
        $str = $this->csvdata[$row][$column];
        $words = explode('/', $str);
        for ($i = 0; $i < sizeof($words); $i++) {
            $words[$i] = self::tokenize($words[$i]);
        }
        return $words;
    }

    public static function tokenize($str) {
        $str = trim($str);
        $str = preg_replace('/\s+/', ' ', $str);
        $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
        $str = strtolower($str);
        return $str;
    }

}
