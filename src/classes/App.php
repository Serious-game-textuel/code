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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Cell_Exception.php');


class App implements App_Interface {

    private int $deaths;
    private int $actions;
    private DateTime $starttime;
    private Default_Action_Interface $defaultactionsearch;
    private Default_Action_Interface $defaultactioninteract;
    private array $visitedlocations;

    private Game_Interface $game;

    private Game_Interface $save;

    private array $csvdata;

    private static App_Interface $instance;

    private array $startentities;

    private static string $playerkeyword;
    private static string $deadkeyword;
    private static string $victorykeyword;

    private string $language;
    private $actionsdone = [];

    public function __construct($csvcontent) {
        $this->deaths = 0;
        $this->actions = 0;
        $this->visitedlocations = [];
        $this->csvdata = array_map('str_getcsv', explode("\n", $csvcontent));
        self::$instance = $this;
        $this->startentities = [];
        $this->language = $this->get_cell_string(2, 1);
        if (strlen($this->language) == 0) {
            throw new Exception("Language not found");
        }
        if ($this->language == Language::FR) {
            self::$playerkeyword = "joueur";
            self::$deadkeyword = "dead";
            self::$victorykeyword = "victory";
        } else {
            self::$playerkeyword = "player";
            self::$deadkeyword = "mort";
            self::$victorykeyword = "victoire";
        }
        $this->parse();
    }

    public function get_deaths() {
        return $this->deaths;
    }
    public function add_death() {
        $this->deaths ++;
    }

    public function get_actions() {
        return $this->actions;
    }

    public function add_action() {
        $this->actions ++;
    }

    public function get_starttime() {
        return $this->starttime;
    }
    public function set_starttime(DateTime $starttime) {
        $this->starttime = $starttime;
    }

    public function get_defaultactionsearch() {
        return $this->defaultactionsearch;
    }

    public function get_defaultactioninteract() {
        return $this->defaultactioninteract;
    }

    public function get_visitedlocations() {
        return $this->visitedlocations;
    }

    public function add_visitedlocation(Location_Interface $location) {
        array_push($this->visitedlocations, $location);
        $this->visitedlocations = Util::clean_array($this->visitedlocations, Location_Interface::class);
    }

    public function get_language() {
        return $this->language;
    }

    public function store_actionsdone($actionsdone) {
        $this->actionsdone[] = $actionsdone;
    }

    public function do_actionsdone($actionsdone) {
        foreach ($actionsdone as $action) {
            $this->get_game()->get_player()->get_currentlocation()->check_actions($action);
        }
    }
    public function get_actionsdone() {
        return $this->actionsdone;
    }
    public static function get_instance() {
        if (isset(self::$instance)) {
            return self::$instance;
        } else {
            throw new Exception("App not initialized");
        }
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

    public function create_save() {
        $this->set_save(clone $this->get_game());
    }

    public function get_startentity($entityname) {
        foreach ($this->startentities as $e) {
            if ($e->get_name() == $entityname) {
                return $e;
            }
        }
        return null;
    }

    public function get_startentities() {
        return $this->startentities;
    }

    public function add_startentity(Entity_Interface $entity) {
        array_push($this->startentities, $entity);
        $this->startentities = Util::clean_array($this->startentities, Entity_Interface::class);
    }

    private function parse() {
        if ($this->language == Language::FR) {
            $itemsrow = $this->get_row("OBJETS");
            $charactersrow = $this->get_row("PERSONNAGES");
            $locationsrow = $this->get_row("LIEUX");
            $defaultactioninteractrow = $this->get_row("Interaction avec objet n'existant pas");
            $defaultactionsearchrow = $this->get_row("Fouiller par défaut");
        } else {
            $itemsrow = $this->get_row("ITEMS");
            $charactersrow = $this->get_row("CHARACTERS");
            $locationsrow = $this->get_row("LOCATIONS");
            $defaultactioninteractrow = $this->get_row("Interaction with non-existent object");
            $defaultactionsearchrow = $this->get_row("Search by default");
        }
        $this->create_items($itemsrow);
        $this->create_characters($charactersrow);
        $this->create_locations($locationsrow);
        $this->check_items_duplicates();
        $this->create_all_actions($locationsrow);
        $this->defaultactioninteract = $this->create_action_defaut($defaultactioninteractrow);
        $this->defaultactionsearch = $this->create_action_defaut($defaultactionsearchrow);

        $player = $this->get_startentity(self::$playerkeyword);
        if ($player->get_currentlocation() == null) {
            throw new Exception("One location must have '" . self::$playerkeyword . "' in their list of characters.");
        }

        $this->starttime = new DateTime();

        $this->restart_game_from_start();
    }

    private function create_action_defaut($row) {
        $description = $this->get_cell_string($row, 1);
        if (strlen($description) > 0) {
            $action = new Default_Action($description, [new Condition([])]);
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
                try {
                    new Item($description, $name, $statuses);
                } catch (Exception $e) {
                    throw new Cell_Exception($e->getMessage(), $row, $col);
                }
            }
            $col++;
        }
    }

    private function create_characters($row) {
        $col = 1;
        $nplayers = 0;
        while (array_key_exists($col, $this->csvdata[$row]) && $this->csvdata[$row][$col] != null) {
            $name = $this->get_cell_string($row, $col);
            $description = $this->get_cell_string($row + 1, $col);
            $statuses = $this->get_cell_array_string($row + 2, $col);
            $itemnames = $this->get_cell_array_string($row + 3, $col);
            $items = [];
            foreach ($itemnames as $itemname) {
                $item = $this->get_startentity($itemname);
                if ($item == null || !($item instanceof Item)) {
                    $errormessage = $itemname . " is not an item";
                    if ($this->language == Language::FR) {
                        $errormessage = $itemname . " n'est pas un objet";
                    }
                    throw new Cell_Exception($errormessage, $row + 3, $col);
                }
                array_push($items, $item);
            }
            if ($name == self::$playerkeyword) {
                new Player_Character($description, $name, $statuses, $items, null);
                $nplayers++;
            } else {
                try {
                    new Npc_Character($description, $name, $statuses, $items, null);
                } catch (Exception $e) {
                    throw new Cell_Exception($e->getMessage(), $row, $col);
                }
            }
            $col++;
        }
        if ($nplayers < 1 || $nplayers > 1) {
            throw new Exception("One character must be named '" . self::$playerkeyword . "'.");
        }
    }

    private function create_locations($row) {
        $col = 1;
        while (array_key_exists($col, $this->csvdata[$row]) && $this->csvdata[$row][$col] != null) {
            $name = $this->get_cell_string($row, $col);
            $statuses = $this->get_cell_array_string($row + 1, $col);
            $itemnames = $this->get_cell_array_string($row + 2, $col);
            $items = [];
            foreach ($itemnames as $itemname) {
                $item = $this->get_startentity($itemname);
                if ($item == null || !($item instanceof Item)) {
                    $errormessage = $itemname . "is not an item";
                    if ($this->language == Language::FR) {
                        $errormessage = $itemname . " n'est pas un objet";
                    }
                    throw new Cell_Exception($errormessage, $row + 2, $col);
                }
                array_push($items, $item);
            }
            $hints = [];
            if ($this->csvdata[$row + 4][$col] != null) {
                $hints = explode('/', $this->csvdata[$row + 4][$col]);
                for ($i = 0; $i < count($hints); $i++) {
                    $hints[$i] = new Hint($hints[$i]);
                }
            }
            try {
                new Location($name, $statuses, $items, $hints, [], 0);
            } catch (Exception $e) {
                throw new Cell_Exception($e->getMessage(), $row, $col);
            }
            $col++;
        }
        $col = 1;
        while (array_key_exists($col, $this->csvdata[$row]) && $this->csvdata[$row][$col] != null) {
            $locationname = $this->get_cell_string($row, $col);
            $location = $this->get_startentity($locationname);
            if ($location == null || !($location instanceof Location)) {
                $errormessage = $locationname . " is not a location";
                if ($this->language == Language::FR) {
                    $errormessage = $locationname . " n'est pas un lieu";
                }
                throw new Cell_Exception($errormessage, $row, $col);
            }
            $characternames = $this->get_cell_array_string($row + 3, $col);
            foreach ($characternames as $name) {
                $character = $this->get_startentity($name);
                if ($character == null || !($character instanceof Character)) {
                    $errormessage = $name . " is not a character";
                    if ($this->language == Language::FR) {
                        $errormessage = $name . " n'est pas un personnage";
                    }
                    throw new Cell_Exception($errormessage, $row + 3, $col);
                }
                $character->set_currentlocation($location);
            }
            $col++;
        }
    }

    private function check_items_duplicates() {
        $itemsentities = [];
        foreach ($this->startentities as $e) {
            if ($e instanceof Item) {
                continue;
            }
            $ename = $e->get_name();
            foreach ($e->get_inventory()->get_items() as $i) {
                $iname = $i->get_name();
                if (array_key_exists($iname, $itemsentities)) {
                    throw new Exception(
                        "'" . $itemsentities[$iname] . "' and '" . $ename . "' have the same item : '" . $iname . "'."
                    );
                }
                $itemsentities[$iname] = $ename;
            }
        }
    }

    private function create_all_actions($row) {
        $col = 1;
        while (array_key_exists($col, $this->csvdata[$row]) && $this->csvdata[$row][$col] != null) {
            $locationname = $this->get_cell_string($row, $col);
            $location = $this->get_startentity($locationname);
            if ($location == null || !($location instanceof Location)) {
                $errormessage = $locationname . " is not a location";
                if ($this->language == Language::FR) {
                    $errormessage = $locationname . " n'est pas un lieu";
                }
                throw new Cell_Exception($errormessage, $row, $col);
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
                    $errormessage = $entityname . " is not an entity";
                    if ($this->language == Language::FR) {
                        $errormessage = $entityname . " n'est pas une entité";
                    }
                    throw new Exception($errormessage, $row + 3, $col);
                }

                $newstatuses = $this->get_cell_array_string($row + 4, $col);

                $oldstatuses = $this->get_cell_array_string($row + 5, $col);

                $newitemnames = $this->get_cell_array_string($row + 6, $col);
                $newitems = [];
                foreach ($newitemnames as $name) {
                    $item = $this->get_startentity($name);
                    if ($item == null || !($item instanceof Item_Interface)) {
                        $errormessage = $name . " is not an item";
                        if ($this->language == Language::FR) {
                            $errormessage = $name . " n'est pas un objet";
                        }
                        throw new Cell_Exception($errormessage, $row + 6, $col);
                    }
                    array_push($newitems, $item);
                }

                $olditemnames = $this->get_cell_array_string($row + 7, $col);
                $olditems = [];
                foreach ($olditemnames as $name) {
                    $item = $this->get_startentity($name);
                    if ($item == null || !($item instanceof Item_Interface)) {
                        $errormessage = $name . " is not an item";
                        if ($this->language == Language::FR) {
                            $errormessage = $name . " n'est pas un objet";
                        }
                        throw new Cell_Exception($errormessage, $row + 7, $col);
                    }
                    array_push($olditems, $item);
                }

                if ($entity instanceof Location_Interface) {
                    $reaction = new Location_Reaction(
                        $reactiondescription, $oldstatuses, $newstatuses, $olditems, $newitems, $entity
                    );
                    if (!isset($reactions[$action][$condition])) {
                        $reactions[$action][$condition] = [];
                    }
                    array_push($reactions[$action][$condition], $reaction);
                } else if ($entity instanceof Character_Interface) {
                    $locationname = $this->get_cell_string($row + 8, $col);
                    $location = null;
                    if ($locationname != "") {
                        $location = $this->get_startentity($locationname);
                        if ($location == null || !($location instanceof Location_Interface)) {
                            $errormessage = $locationname . " is not a location";
                            if ($this->language == Language::FR) {
                                $errormessage = $locationname . " n'est pas un lieu";
                            }
                            throw new Cell_Exception($errormessage, $row + 8, $col);
                        }
                    }
                    $reaction = new Character_Reaction($reactiondescription,
                    $oldstatuses, $newstatuses, $olditems, $newitems, $entity, $location);
                    if (!isset($reactions[$action][$condition])) {
                        $reactions[$action][$condition] = [];
                    }
                    array_push($reactions[$action][$condition], $reaction);
                } else {
                    throw new Cell_Exception($entityname . " must be a location or a character", $row + 3, $col);
                }

            } else {
                $reaction = new No_Entity_Reaction($reactiondescription);
                if (!isset($reactions[$action][$condition])) {
                    $reactions[$action][$condition] = [];
                }
                array_push($reactions[$action][$condition], $reaction);
            }

            $row += $rowstep;
        }

        foreach ($descriptions as $action) {
            $conditions = [];
            foreach ($conditionnames[$action] as $condition) {
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
        if ($condition == "NO_CONDITION") {
            return new Leaf_Condition(null, null, "", [], $reactions);
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
            if ($this->language == Language::FR) {
                throw new Exception("Erreur d'algorithme : les jetons ne doivent pas être vides");
            } else {
                throw new Exception("Algorithm error : tokens should not be empty");
            }
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
                $this->read_tree($tree[1], $reactions),
                $this->read_tree($tree[0], $reactions),
                $tree[2],
                $reactions
            );
        } else {
            if ($this->language == Language::FR) {
                throw new Exception("Erreur d'algorithme");
            } else {
                throw new Exception("Algorithm error");
            }
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
            if ($this->language == Language::FR) {
                throw new Exception("Syntaxe de condition incorrecte : " . $condition);
            } else {
                throw new Exception("Wrong condition syntax : " . $condition);
            }
        }

        $connector .= $tokens[$connectorstart];
        if ($this->language == Language::FR) {
            if ($tokens[$connectorstart + 1] == "pas") {
                $connector .= ' ' . $tokens[$connectorstart + 1];
                $member2start++;
            }
        } else {
            if ($tokens[$connectorstart + 1] == "not") {
                $connector .= ' ' . $tokens[$connectorstart + 1];
                $member2start++;
            }
        }
        $member2 = implode(' ', array_slice($tokens, $member2start));
        $entity2 = $this->get_startentity($member2);
        $status = "";
        if ($entity2 == null) {
            $status = $member2;
        }
        return new Leaf_Condition($entity1, $entity2, $connector, array_filter([$status]), $reactions);
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
            if ($this->language == Language::FR) {
                throw new Exception($str . " ligne non trouvée");
            } else {
                throw new Exception($str . " line not found");
            }
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
        $this->game = new Game($this->startentities);
    }

    public function restart_game_from_save() {
        $this->set_game(clone $this->get_save());
    }
}
