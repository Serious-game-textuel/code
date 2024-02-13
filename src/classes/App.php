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

    private static string $playerkeyword;

    private Language $language;

    public function __construct($csvdata, Language $language) {
        self::$instance = $this;
        $this->csvdata = $csvdata;
        $this->language = $language;
        $this->startentities = [];
        if ($this->language == Language::FR) {
            self::$playerkeyword = "joueur";
        } else {
            self::$playerkeyword = "player";
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

        $itemsrow = $this->get_row("OBJETS");
        $charactersrow = $this->get_row("PERSONNAGES");
        $locationsrow = $this->get_row("LIEUX");

        $this->create_items($itemsrow);
        $this->create_characters($charactersrow);
        $this->create_locations($locationsrow);
        $this->create_all_actions($locationsrow);

        $player = $this->get_startentity($this->playerkeyword);

        $this->game = new Game(0, 0, [$player->get_current_location()], new DateTime(), $player, null, null, $this->startentities);
        
    }

    private function create_items($row) {
        $col = 1;
        $row = $this->get_row("OBJETS");
        while ($this->csvdata[$row][$col] != null) {
            $name = $this->get_cell_string(3, $col);
            $description = $this->get_cell_string(4, $col);
            $statuses = $this->get_cell_array_string(5, $col);
            new Item($description, $name, $statuses);
            $col++;
        }
    }

    private function create_characters($row) {
        $col = 1;
        while ($this->csvdata[$row][$col] != null) {
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

    private function create_locations($row) {
        $col = 1;
        while ($this->csvdata[$row][$col] != null) {
            $name = $this->get_cell_string($row, $col);
            $statuses = $this->get_cell_array_string($row + 1, $col);
            $itemnames = $this->get_cell_array_string($row + 2, $col);
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
        while ($this->csvdata[$row][$col] != null) {
            $locationname = $this->get_cell_string($row, $col);
            $location = $this->get_startentity($locationname);
            if ($location == null || !($location instanceof Location)) {
                throw new Exception($locationname . "is not a location");
            }
            $character = $this->get_startentity($name);
            $characternames = $this->get_cell_array_string($row + 3, $col);
            foreach ($characternames as $name) {
                $character = $this->get_startentity($name);
                if ($character == null || !($character instanceof Character)) {
                    throw new Exception($name . "is not a character");
                }
                $character->set_currentlocation($location);
            }
        }
    }

    private function create_all_actions($row) {
        $col = 1;
        while ($this->csvdata[$row][$col] != null) {
            $locationname = $this->get_cell_string($row, $col);
            $location = $this->get_startentity($locationname);
            if ($location == null || !($location instanceof Location)) {
                throw new Exception($locationname . "is not a location");
            }
            $actions = $this->create_column_actions($location, $col, $row + 6);

            $col++;
        }
    }

    private function create_column_actions($location, $col, $row) {
        $rowstep = 10;

        $actions = [];
        $descriptions = [];
        $conditions = [];
        $reactions = [];

        while ($this->csvdata[$row][$col] != null) {

            $action = $this->get_cell_string($row, $col);
            if (!in_array($action, $descriptions)) {
                array_push($descriptions, $action);
            }

            $condition = $this->get_cell_string($row + 1, $col);
            array_push($conditions[$action], $condition);

            $reactiondescription = $this->get_cell_string($row + 2, $col);

            $entity = $this->get_startentity($this->get_cell_string($row + 3, $col));
            if ($entity == null) {
                throw new Exception($this->get_cell_string($row + 3, $col) . " is not an entity");
            }

            $newstatuses = $this->get_cell_array_string($row + 4, $col);

            $oldstatuses = $this->get_cell_array_string($row + 5, $col);

            $newitemnames = $this->get_cell_array_string($row + 6, $col);
            $newitems = [];
            foreach ($newitemnames as $name) {
                $item = $this->get_startentity($name);
                if ($item == null || !($item instanceof Item_Interface)) {
                    throw new Exception($name . " is not an Item");
                }
                array_push($newitems, $item);
            }

            $olditemnames = $this->get_cell_array_string($row + 7, $col);
            $olditems = [];
            foreach ($olditemnames as $name) {
                $item = $this->get_startentity($name);
                if ($item == null || !($item instanceof Item_Interface)) {
                    throw new Exception($name . " is not an Item");
                }
                array_push($olditems, $item);
            }

            

            if ($entity instanceof Location_Interface) {
                $reaction = new Location_Reaction($reactiondescription, $oldstatuses, $newstatuses, $olditems, $newitems, $entity);
                array_push($reactions[$action][$condition], $reaction);
            } else if ($entity instanceof Character_Interface) {
                $location = $this->get_startentity($this->get_cell_string($row + 8, $col));
                if ($location == null || !($location instanceof Location_Interface)) {
                    throw new Exception($name . " is not a Location");
                }

                $reaction = new Character_Reaction($reactiondescription, $oldstatuses, $newstatuses, $olditems, $newitems, $entity, $location);
                array_push($reactions[$action][$condition], $reaction);
            } else {
                throw new Exception("Only characters and locations can have reactions");
            }

            $row += $rowstep;
        }

        foreach ($descriptions as $action) {
            $conditions = [];
            foreach ($conditions[$action] as $condition) {
                
                array_push($conditions,
                    $this->parse_condition(
                        $condition,
                        $reactions[$action][$condition]
                    )
                );
            }
            array_push($actions, new Action($action, $conditions));
        }
        return $actions;
    }

    private function parse_condition($condition, $reactions) {
        $tokens = $this->get_tokens($condition);
        
        //Shunting Yard
        $output = [];
        $stack = [];
        foreach ($tokens as $t) {
            if ($t == '|') {
                while (!empty($stack) && (end($stack) == "|" || end($stack) == "&")) {
                    $output[] = array_pop($stack);
                }
                $stack[] = $t;
            } else if ($t == '&') {
                while (!empty($stack) && end($stack) == "&") {
                    $output[] = array_pop($stack);
                }
                $stack[] = $t;
            } else if ($t == '(') {
                $stack[] = $t;
            } else if ($t == ')') {
                while (!empty($stack) && end($stack) != '(') {
                    $output[] = array_pop($stack);
                }
                array_pop($stack);
            } else {
                $output[] = $t;
            }
        }
        while (!empty($stack)) {
            $output[] = array_pop($stack);
        }

        return $this->read_tree($output, $reactions);
    }

    private function read_tree($tokens, $reactions) {
        if (empty($tokens)) {
            return null;
        } else if (end($tokens) == '|' || end($tokens) == '&') {
            $token = array_pop($tokens);
            return new Node_Condition($this->read_tree($tokens, $reactions), $this->read_tree($tokens, $reactions), $token, $reactions);
        } else {
            $token = array_pop($tokens);
            return $this->parse_leaf_condition($token, $reactions);
        }
    }

    private function parse_leaf_condition($condition, $reactions) {
        $tokens = $this->get_tokens($condition);
        $connectortokenstart = 0;
        $member2tokenstart = 0;
        $connector = "";
        for ($i = 0; $i < count($tokens); $i++) {
            if ($tokens[$i] == "a" || $tokens[$i] == "est") {
                $connectortokenstart = $i;
                $connector .= $tokens[$i];
                if ($tokens[$i+1] == "pas") {
                    $member2tokenstart = $i + 2;
                    $connector .= " pas";
                } else {
                    $member2tokenstart = $i + 1;
                }
            }
        }

        $member1 = implode(' ', array_slice($tokens, 0, $connectortokenstart));
        $member2 = implode(' ', array_slice($tokens, $member2tokenstart));

        $entity1 = $this->get_startentity($member1);
        if ($entity1 == null) {
            throw new Exception($member1 . " is not an entity");
        }
        $entity2 = $this->get_startentity($member2);
        $status = "";
        if ($entity1 == null) {
            $status = $member2;
        }
        return new Leaf_Condition($entity1, $entity2, $connector, [$status], $reactions);
    }

    private function get_row($str) {
        $lign = 0;
        $foundline = false;
        while (!$foundline && sizeof($this->csvdata)>$lign) {
            if (strcmp($this->csvdata[$lign][0], $str) != 0) {
                $foundline = true;
            } else {
                $lign = $lign + 1;
            }
        }
        if (!$foundline) {
            throw new Exception($str . " line not found");
        }
        return $lign;
    }

    private function get_cell_string($row, $column) {
        return self::tokenize($this->csvdata[$row][$column]);
    }

    private function get_tokens($str) {
        $tokens = [];
        $token = "";
        for ($i = 0; $i < strlen($str); $i++) {
            $c = $str[$i];
            if ($c == "(" || $c == ")" || $c == "&" || $c == "|") {
                $token = self::tokenize($token);
                if ($token != "") {
                    array_push($tokens, $token);
                }
                $token = "";
                array_push($tokens, $c);
            } else {
                $token .= $c;
            }
        }
        $token = self::tokenize($token);
        if ($token != "") {
            array_push($tokens, $token);
        }
        return $tokens;
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