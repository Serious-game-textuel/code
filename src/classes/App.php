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
global $CFG;
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/App_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Item.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Player_Character.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Npc_Character.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Location.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/No_Entity_Reaction.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Character_Reaction.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Location_Reaction.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Leaf_Condition.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Node_Condition.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Action.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Game.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Default_Action.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Util.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Node_Condition.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Hint.php');


class App implements App_Interface {

    private $id;
    private array $csvdata;

    public function __construct(?int $id, ?string $csvfilepath, ?string $language) {
        global $DB;
        if (!isset($id)) {
            $file = fopen($csvfilepath, 'r');
            if ($file !== false) {
                $this->csvdata = [];
                while (($line = fgetcsv($file)) !== false) {
                    $this->csvdata[] = $line;
                }
                fclose($file);
                $playerkeyword = "";
                if ($language == Language::FR) {
                    $playerkeyword = "joueur";
                } else {
                    $playerkeyword = "player";
                }
                global $USER;
                $sql = "select id from {language} where " . $DB->sql_compare_text('name') . " = ".$DB->sql_compare_text(':name');
                $languageid = $DB->get_field_sql($sql, ['name' => $language]);
                $this->id = $DB->insert_record('app', [
                    'studentid' => $USER->id,
                    'language_id' => $languageid,
                    'playerkeyword' => $playerkeyword,
                ]);
                $this->parse();
            } else {
                throw new Exception("File not found");
            }
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {app} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No App object of ID:".$id." exists.");
            }
            $this->id = $id;
        }
    }

    public static function get_instance() {
        global $DB;
        global $USER;
        $sql = "select id from {app} where " . $DB->sql_compare_text('studentid') . " = ".$DB->sql_compare_text(':studentid');
        $id = $DB->get_field_sql($sql, ['studentid' => $USER->id]);
        if ($id > 0) {
            return new App($id, null, null);
        } else {
            return null;
        }
    }

    public function get_game() {
        global $DB;
        $sql = "select game_id from {app} where " . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        $gameid = $DB->get_field_sql($sql, ['id' => $this->get_id()]);
        return Game::get_instance($gameid);
    }

    public function set_game(Game_Interface $game) {
        global $DB;
        $DB->set_field('app', 'game_id', $game->get_id(), ['id' => $this->get_id()]);
    }

    public function get_startentity($entityname) {
        global $DB;
        $sql = "select {entity}.id from {app_startentities} left join {entity} "
        . "on {app_startentities}.entity_id = {entity}.id where "
        . $DB->sql_compare_text('{entity}.name') . " = ".$DB->sql_compare_text(':entityname') . " and "
        . $DB->sql_compare_text('{app_startentities}.app_id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $this->get_id(), 'entityname' => $entityname]);
        if ($id > 0) {
            return Entity::get_instance($id);
        } else {
            return null;
        }
    }

    public function get_startentities() {
        $startentities = [];
        global $DB;
        $sql = "select {entity}.id from {app_startentities} left join {entity} on {app_startentities}.entity_id = {entity}.id where "
        . $DB->sql_compare_text('{app_startentities}.app_id') . " = ".$DB->sql_compare_text(':id');
        $ids = $DB->get_fieldset_sql($sql, ['id' => $this->get_id()]);
        foreach ($ids as $id) {
            array_push($startentities, Entity::get_instance($id));
        }
        return $startentities;
    }

    public function add_startentity(Entity_Interface $entity) {
        global $DB;
        $DB->insert_record('app_startentities', [
            'app_id' => $this->get_id(),
            'entity_id' => $entity->get_id(),
        ]);
    }

    public function add_startentity_from_id(int $entityid) {
        global $DB;
        $DB->insert_record('app_startentities', [
            'app_id' => $this->get_id(),
            'entity_id' => $entityid,
        ]);
    }

    private function parse() {
        $itemsrow = $this->get_row("OBJETS");
        $charactersrow = $this->get_row("PERSONNAGES");
        $locationsrow = $this->get_row("LIEUX");
        $interactiondefautrow = $this->get_row("interaction avec objet n'existant pas :");
        $fouillerdefautrow = $this->get_row("Fouiller par défaut :");
        $this->create_items($itemsrow);
        $this->create_characters($charactersrow);
        $this->create_locations($locationsrow);
        $this->create_all_actions($locationsrow);
        $interactiondefaut = $this->create_action_defaut($interactiondefautrow);
        $fouillerdefaut = $this->create_action_defaut($fouillerdefautrow);

        global $DB;
        $sql = "select playerkeyword from {app} where "
        . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        $playerkeyword = $DB->get_field_sql($sql, ['id' => $this->get_id()]);
        $player = $this->get_startentity($playerkeyword);
        if ($player != null) {
            try {
                $character = Character::get_instance_from_parent_id($player->get_id());
                try {
                    $player = Player_Character::get_instance_from_parent_id($character->get_id());
                } catch (Exception $e) {
                    throw new Exception('No player found in this game');
                }
            } catch (Exception $e) {
                throw new Exception('No player found in this game');
            }
        }
        $arguments = [];
        if (isset($fouillerdefaut)) {
            $arguments = array_merge($arguments, ['defaultactionsearch' => $fouillerdefaut]);
        }
        if (isset($interactiondefaut)) {
            $arguments = array_merge($arguments, ['defaultactioninteract' => $interactiondefaut]);
        }
        new Game(null, 0, 0, [], new DateTime(), $player, $arguments['defaultactionsearch']
        , $arguments['defaultactioninteract'], $this->get_startentities());
    }

    private function create_action_defaut($row) {
        $description = $this->get_cell_string($row, 1);
        if (strlen($description) > 0) {
            $action = new Default_Action(null, $description, [new Condition(null, [])]);
            return $action;
        } else {
            return null;
        }
    }

    private function create_items($row) {
        $col = 1;
        while (array_key_exists($col, $this->csvdata[$row]) && $this->csvdata[$row][$col] != null) {
            $name = $this->get_cell_string($row, $col);
            $description = $this->get_cell_string($row + 1, $col);
            $statuses = $this->get_cell_array_string($row + 2, $col);
            if ($name != null && strlen($name) > 0) {
                new Item(null, $description, $name, $statuses);
            }
            $col++;
        }
    }

    private function create_characters($row) {
        $col = 1;
        global $DB;
        $sql = "select playerkeyword from {app} where "
        . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        $playerkeyword = $DB->get_field_sql($sql, ['id' => $this->get_id()]);
        while (array_key_exists($col, $this->csvdata[$row]) && $this->csvdata[$row][$col] != null) {
            $name = $this->get_cell_string($row, $col);
            $description = $this->get_cell_string($row + 1, $col);
            $statuses = $this->get_cell_array_string($row + 2, $col);
            $itemnames = $this->get_cell_array_string($row + 3, $col);
            $items = [];
            foreach ($itemnames as $itemname) {
                $item = $this->get_startentity($itemname);
                if ($item != null) {
                    try {
                        $item = Item::get_instance_from_parent_id($item->get_id());
                    } catch (Exception $e) {
                        throw new Exception($itemname . " is not an item with the row: " . $row . " and the col: " . $col ."");
                    }
                } else {
                    throw new Exception($itemname . " is not an item with the row: " . $row . " and the col: " . $col ."");
                }
                array_push($items, $item);
            }
            if ($name == $playerkeyword) {
                new Player_Character(null, $description, $name, $statuses, $items, null);
            } else {
                new Npc_Character(null, $description, $name, $statuses, $items, null);
            }
            $col++;
        }
    }

    private function create_locations($row) {
        $col = 1;
        global $DB;
        while (array_key_exists($col, $this->csvdata[$row]) && $this->csvdata[$row][$col] != null) {
            $name = $this->get_cell_string($row, $col);
            $statuses = $this->get_cell_array_string($row + 1, $col);
            $itemnames = $this->get_cell_array_string($row + 2, $col);
            $items = [];
            foreach ($itemnames as $itemname) {
                $item = $this->get_startentity($itemname);
                if ($item != null) {
                    try {
                        $item = Item::get_instance_from_parent_id($item->get_id());
                    } catch (Exception $e) {
                        throw new Exception($itemname . "is not an item and here is the row: " . $row . " and the col: " . $col ."");
                    }
                } else {
                    throw new Exception($itemname . "is not an item and here is the row: " . $row . " and the col: " . $col ."");
                }
                array_push($items, $item);
            }
            $hints = [];
            if ($this->csvdata[$row + 4][$col] != null) {
                $hints = explode('/', $this->csvdata[$row + 4][$col]);
                for ($i = 0; $i < count($hints); $i++) {
                    $hints[$i] = new Hint(null, $hints[$i]);
                }
            }
            new Location(null, $name, $statuses, $items, $hints, [], 0);
            $col++;
        }
        $col = 1;
        while (array_key_exists($col, $this->csvdata[$row]) && $this->csvdata[$row][$col] != null) {
            $locationname = $this->get_cell_string($row, $col);
            $location = $this->get_startentity($locationname);
            if ($location != null) {
                try {
                    $location = Location::get_instance_from_parent_id($location->get_id());
                } catch (Exception $e) {
                    throw new Exception($locationname . "is not a location");
                }
            } else {
                throw new Exception($locationname . "is not a location");
            }
            $character = $this->get_startentity($name);
            $characternames = $this->get_cell_array_string($row + 3, $col);
            foreach ($characternames as $name) {
                $character = $this->get_startentity($name);
                if ($character != null) {
                    try {
                        $character = Character::get_instance_from_parent_id($character->get_id());
                    } catch (Exception $e) {
                        throw new Exception($name . " is not a character");
                    }
                } else {
                    throw new Exception($name . " is not a character");
                }
                $character->set_currentlocation($location);
            }
            $col++;
        }
    }

    private function create_all_actions($row) {
        $col = 1;
        global $DB;
        while (array_key_exists($col, $this->csvdata[$row]) && $this->csvdata[$row][$col] != null) {
            $locationname = $this->get_cell_string($row, $col);
            $location = $this->get_startentity($locationname);
            if ($location != null) {
                try {
                    $location = Location::get_instance_from_parent_id($location->get_id());
                } catch (Exception $e) {
                    throw new Exception($locationname . "is not a location");
                }
            } else {
                throw new Exception($locationname . "is not a location");
            }
            $actions = $this->create_column_actions($location, $col, $row + 6);
            $location->set_actions($actions);
            $col++;
        }
    }

    private function create_column_actions($location, $col, $row) {
        $rowstep = 10;
        $actions = [];
        $descriptions = [];
        $conditionnames = [];
        $reactions = [];
        global $DB;
        while (array_key_exists($col, $this->csvdata[$row]) && $this->csvdata[$row][$col] != null) {

            $action = $this->get_cell_string($row, $col);
            if (!in_array($action, $descriptions)) {
                array_push($descriptions, $action);
            }
            $condition = $this->get_cell_string($row + 1, $col);
            if ($condition == null) {
                $condition = "NO_CONDITION";
            }
            if (!isset($conditionnames[$action])) {
                $conditionnames[$action] = [];
            }
            if (!in_array($condition, $conditionnames[$action])) {
                array_push($conditionnames[$action], $condition);
            }

            $reactiondescription = $this->get_cell_string($row + 2, $col);

            $entityname = $this->get_cell_string($row + 3, $col);

            $entity = null;
            $newstatuses = null;
            $oldstatuses = null;
            $newitems = [];
            $olditems = [];
            if ($entityname != "") {
                $entity = $this->get_startentity($entityname);
                if ($entity == null) {
                    throw new Exception($this->get_cell_string($row + 3, $col)
                    . " is not an entity with the row: " . $row . " and the col: " . $col ."");
                }

                $newstatuses = $this->get_cell_array_string($row + 4, $col);

                $oldstatuses = $this->get_cell_array_string($row + 5, $col);

                $newitemnames = $this->get_cell_array_string($row + 6, $col);
                $newitems = [];
                foreach ($newitemnames as $name) {
                    $item = $this->get_startentity($name);
                    if ($item != null) {
                        try {
                            $item = Item::get_instance_from_parent_id($item->get_id());
                        } catch (Exception $e) {
                            throw new Exception($name . " is not an Item");
                        }
                    } else {
                        throw new Exception($name . " is not an Item");
                    }
                    array_push($newitems, $item);
                }
                $olditemnames = $this->get_cell_array_string($row + 7, $col);
                $olditems = [];
                foreach ($olditemnames as $name) {
                    $item = $this->get_startentity($name);
                    if ($item != null) {
                        try {
                            $item = Item::get_instance_from_parent_id($item->get_id());
                        } catch (Exception $e) {
                            throw new Exception($name . " is not an Item");
                        }
                    } else {
                        throw new Exception($name . " is not an Item");
                    }
                    array_push($olditems, $item);
                }
            }
            if ($entity != null) {
                $parententity = $entity;
                try  {
                    $entity = Location::get_instance_from_parent_id($parententity->get_id());
                } catch (Exception $e) {
                    try {
                        $entity = Character::get_instance_from_parent_id($parententity->get_id());
                    } catch (Exception $e) {}
                }
            }
            if ($entity instanceof Location_Interface) {
                $reaction = new Location_Reaction(null, $reactiondescription, $oldstatuses, $newstatuses,
                $olditems, $newitems, $entity);
                if (!isset($reactions[$action][$condition])) {
                    $reactions[$action][$condition] = [];
                }
                array_push($reactions[$action][$condition], Reaction::get_instance($reaction->get_parent_id()));
            } else if ($entity instanceof Character_Interface) {
                $locationname = $this->get_cell_string($row + 8, $col);
                $location = null;
                if ($locationname != "") {
                    $location = $this->get_startentity($locationname);
                    if ($location != null) {
                        try {
                            $location = Location::get_instance_from_parent_id($location->get_id());
                        } catch (Exception $e) {
                            throw new Exception($locationname . " is not a location");
                        }
                    } else {
                        throw new Exception($locationname . " is not a location");
                    }
                }
                $reaction = new Character_Reaction(null, $reactiondescription,
                $oldstatuses, $newstatuses, $olditems, $newitems, $entity, $location);
                if (!isset($reactions[$action][$condition])) {
                    $reactions[$action][$condition] = [];
                }
                array_push($reactions[$action][$condition], Reaction::get_instance($reaction->get_parent_id()));
            } else if ($entityname == "") {
                $reaction = new No_Entity_Reaction(null, $reactiondescription);
                if (!isset($reactions[$action][$condition])) {
                    $reactions[$action][$condition] = [];
                }
                array_push($reactions[$action][$condition], Reaction::get_instance($reaction->get_parent_id()));
            } else {
                throw new Exception("Only characters and locations can have reactions");
            }
            $row = $row + $rowstep;
        }

        foreach ($descriptions as $action) {
            $conditions = [];
            foreach ($conditionnames[$action] as $condition) {
                $cond = $this->parse_condition(
                    $condition,
                    $reactions[$action][$condition]
                );
                array_push($conditions, Condition::get_instance($cond->get_parent_id()));
            }
            array_push($actions, new Action(null, $action, $conditions));
        }
        return $actions;
    }

    private function parse_condition($condition, $reactions) {
        if ($condition == "NO_CONDITION") {
            return new Leaf_Condition(null, null, null, "", [], $reactions);
        }
        $tokens = $this->get_condition_tokens($condition);
        // Shunting Yard.
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

        return $this->create_condition($output, $reactions);
    }

    private function create_condition($tokens, $reactions) {
        $tree = $this->build_tree($tokens);
        return $this->read_tree($tree, $reactions);
    }

    private function build_tree($tokens) {
        if (empty($tokens)) {
            throw new Exception("Algorithm error : tokens should not be empty");
        }
        $token = array_pop($tokens);
        if ($token != '|' && $token != '&') {
            return [null, null, $token, $tokens];
        } else {
            $tree1 = $this->build_tree($tokens);
            $tree2 = $this->build_tree($tree1[3]);
            return [$tree1, $tree2, $token, $tokens];
        }
    }

    private function read_tree($tree, $reactions) {
        if ($tree[0] == null && $tree[1] == null) {
            return $this->parse_leaf_condition($tree[2], $reactions);
        } else if ($tree[0] != null && $tree[1] != null) {
            return new Node_Condition(
                null,
                $this->read_tree($tree[1], $reactions),
                $this->read_tree($tree[0], $reactions),
                $tree[2],
                $reactions
            );
        } else {
            throw new Exception("Algorithm error");
        }
    }

    private function parse_leaf_condition($condition, $reactions) {
        $entity1 = null;
        $connector = "";
        $connectorstart = 0;
        $member2start = 0;

        $tokens = explode(' ', $condition);

        for ($i = 1; $i <= count($tokens); $i++) {
            $member1 = implode(' ', array_slice($tokens, 0, $i));
            $entity1 = $this->get_startentity($member1);
            if ($entity1 != null) {
                $connectorstart = $i;
                $member2start = $i + 1;
                break;
            }
        }
        if ($entity1 == null) {
            throw new Exception("Wrong condition syntax : " . $condition);
        }

        $connector .= $tokens[$connectorstart];

        if ($tokens[$connectorstart + 1] == "pas") {
            $connector .= ' ' . $tokens[$connectorstart + 1];
            $member2start++;
        }

        $member2 = implode(' ', array_slice($tokens, $member2start));
        $entity2 = $this->get_startentity($member2);
        $status = "";
        if ($entity2 == null) {
            $status = $member2;
        }
        return new Leaf_Condition(null, $entity1, $entity2, $connector, array_filter([$status]), $reactions);
    }

    private function get_row($str) {
        $lign = 0;
        $foundline = false;
        while (!$foundline && count($this->csvdata) > $lign) {
            if (strcmp($this->csvdata[$lign][0], $str) != 0) {
                $lign = $lign + 1;
            } else {
                $foundline = true;
            }
        }
        if (!$foundline) {
            throw new Exception($str . " line not found");
        }
        return $lign;
    }

    private function get_cell_string($row, $column) {
        $str = $this->csvdata[$row][$column];
        if ($str == null) {
            return "";
        }
        return self::tokenize($this->csvdata[$row][$column]);
    }

    private function get_condition_tokens($str) {
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
        if ($str == null) {
            return [];
        }
        $words = explode('/', $str);
        for ($i = 0; $i < count($words); $i++) {
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

    public function restart_game_from_start() {
        $game = $this->get_game();
        global $DB;
        $DB->delete_records('game_entities', ['game' => $game->get_id()]);

        foreach ($game->get_entities() as $entity) {
            if ($entity != null) {
                try {
                    $entity = Player_Character::get_instance_from_parent_id($entity->get_id());
                    if ($entity instanceof Player_Character) {
                        $game->set_player($entity);
                    }
                } catch (Exception $e) {}
            }
        }
    }

    public function get_id() {
        return $this->id;
    }
}
