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

use core_reportbuilder\external\filters\set;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/mod/stg/src/interfaces/App_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Item.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Player_Character.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Npc_Character.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Location.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/No_Entity_Reaction.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Character_Reaction.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Location_Reaction.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Leaf_Condition.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Node_Condition.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Action.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Game.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Default_Action.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Util.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Node_Condition.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Hint.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Cell_Exception.php');
require_once($CFG->dirroot . '/mod/stg/src/Language.php');

/**
 * Class App
 * @package mod_stg
 * @copyright   2024 Paul Grandhomme, Loric Gallier, Benjamin Bracquier, Mathis Courant
 */
class App implements App_Interface {

    private array $csvdata;
    private static string $playerkeyword;
    private $id;

    public function __construct(?int $id, ?string $csvfilepath, int $deaths, int $actions,
    DateTime $starttime, array $visitedlocations) {
        global $DB;
        $this->init_language();
        if (!isset($id)) {
            $file = fopen($csvfilepath, 'r');
            if ($file !== false) {
                $this->csvdata = [];
                while (($line = fgetcsv($file)) !== false) {
                    $this->csvdata[] = $line;
                }
                fclose($file);
                $playerkeyword = "";
                $language = $this->get_cell_string(2, 1);
                if (strlen($language) == 0) {
                    throw new Exception("Language not found");
                }
                if ($language == Language::FR) {
                    $playerkeyword = "joueur";
                } else {
                    $playerkeyword = "player";
                }
                global $USER;
                $sql = "select id from {stg_language} where " . $DB->sql_compare_text('name') .
                 " = ".$DB->sql_compare_text(':name');
                $starttime = new DateTime();
                $languageid = $DB->get_field_sql($sql, ['name' => $language]);
                $this->id = $DB->insert_record('stg_app', [
                    'studentid' => $USER->id,
                    'language_id' => $languageid,
                    'playerkeyword' => $playerkeyword,
                    'deaths' => $deaths,
                    'actions' => $actions,
                    'starttime' => $starttime->getTimestamp(),
                    'csvfilepath' => $csvfilepath,
                    'activityid' => intval($_POST['module']),
                ]);
                try {
                    $this->parse();
                } catch (Exception $e) {
                    $this->delete_all_data();
                    throw $e;
                }
                $this->set_visited_locations($visitedlocations);
            } else {
                throw new Exception("File not found");
            }
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {stg_app} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No App object of ID:".$id." exists.");
            }
            $this->id = $id;
        }
    }

    public function init_language() {
        global $DB;
        foreach (Language::get_all_languages() as $lang) {
            $comparedescription = $DB->sql_compare_text('name');
            $comparedescriptionplaceholder = $DB->sql_compare_text(':name');
            $todogroups = $DB->record_exists_sql(
                "SELECT id FROM {stg_language} WHERE {$comparedescription} = {$comparedescriptionplaceholder}",
                ['name' => $lang]
            );
            if (!$todogroups) {
                $DB->insert_record('stg_language', ['name' => $lang]);
            }
        }
    }
    public function get_deaths() {
        global $DB;
        $sql = "select deaths from {stg_app} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->id]);
    }
    public function add_deaths() {
        global $DB;
        $DB->set_field('stg_app', 'deaths', $this->get_deaths() + 1, ['id' => $this->id]);
    }

    public function get_actions() {
        global $DB;
        $sql = "select actions from {stg_app} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->id]);
    }

    public function add_action() {
        global $DB;
        $DB->set_field('stg_app', 'actions', $this->get_actions() + 1, ['id' => $this->id]);
    }

    public function get_start_time() {
        global $DB;
        $sql = "select starttime from {stg_app} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        $datetime = new DateTime();
        $datetime->setTimestamp($DB->get_field_sql($sql, ['id' => $this->id]));
        return $datetime;
    }
    public function set_start_time(DateTime $starttime) {
        global $DB;
        $DB->set_field('stg_app', 'starttime', $starttime->getTimestamp(), ['id' => $this->id]);
    }

    public function get_default_action_interact() {
        global $DB;
        $sql = "select defaultactioninteract_id from {stg_app} where "
        . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return Default_Action::get_instance($DB->get_field_sql($sql, ['id' => $this->id]));
    }

    public function set_default_action_interact(Default_Action_Interface $action) {
        global $DB;
        $DB->set_field('stg_app', 'defaultactioninteract_id', $action->get_id(), ['id' => $this->id]);
    }

    public function get_default_action_search() {
        global $DB;
        $sql = "select defaultactionsearch_id from {stg_app} where ".
        $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return Default_Action::get_instance($DB->get_field_sql($sql, ['id' => $this->id]));
    }

    public function set_default_action_search(Default_Action_Interface $action) {
        global $DB;
        $DB->set_field('stg_app', 'defaultactionsearch_id', $action->get_id(), ['id' => $this->id]);
    }
    public function get_visited_locations() {
        $visitedlocations = [];
        global $DB;
        $sql = "select location_id from {stg_game_visitedlocations} where "
        . $DB->sql_compare_text('game_id') . " = ".$DB->sql_compare_text(':id');
        $ids = $DB->get_fieldset_sql($sql, ['id' => $this->get_game()->get_id()]);
        foreach ($ids as $id) {
            array_push($visitedlocations, Location::get_instance($id));
        }
        return $visitedlocations;
    }

    public function set_visited_locations(array $visitedlocations) {
        $visitedlocations = Util::clean_array($visitedlocations, Location_Interface::class);
        global $DB;
        $DB->delete_records('stg_game_visitedlocations', ['game_id' => $this->id]);
        foreach ($visitedlocations as $location) {
            $DB->insert_record('stg_game_visitedlocations', [
                'game_id' => $this->get_game()->get_id(),
                'location_id' => $location->get_id(),
            ]);
        }
    }

    public function add_visited_location(Location_Interface $location) {
        $locations = $this->get_visited_locations();
        array_push($locations, $location);
        $this->set_visited_locations($locations);
    }

    public function get_language_id() {
        global $DB;
        $sql = "SELECT language_id FROM {stg_app} WHERE " . $DB->sql_compare_text('id') . " = " . $DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->id]);
    }
    public function get_language() {
        global $DB;
        $sql = "select name from {stg_language} where " . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->get_language_id()]);
    }

    public static function get_instance() {
        global $DB;
        global $USER;
        $sql = "select id from {stg_app} where " . $DB->sql_compare_text('studentid') . " = ".$DB->sql_compare_text(':studentid')
        . " and " . $DB->sql_compare_text('activityid') . " = ".$DB->sql_compare_text(':activityid');
        $id = $DB->get_field_sql($sql, ['studentid' => $USER->id, 'activityid' => intval($_POST['module'])]);
        if ($id > 0) {
            return new App($id, null, 0, 0, new DateTime(), []);
        } else {
            return null;
        }
    }

    public function get_game() {
        global $DB;
        $sql = "select game_id from {stg_app} where " . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        $gameid = $DB->get_field_sql($sql, ['id' => $this->id]);
        return Game::get_instance($gameid);
    }
    public function get_csvfilepath() {
        global $DB;
        $sql = "select csvfilepath from {stg_app} where " . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        $csvfilepath = $DB->get_field_sql($sql, ['id' => $this->id]);
        return $csvfilepath;
    }

    public function set_game(Game_Interface $game) {
        global $DB;
        $DB->set_field('stg_app', 'game_id', $game->get_id(), ['id' => $this->id]);
    }

    public function get_startentity($entityname) {
        global $DB;
        $sql = "select {stg_entity}.id from {stg_app_startentities} left join {stg_entity} "
        . "on {stg_app_startentities}.entity_id = {stg_entity}.id where "
        . $DB->sql_compare_text('{stg_entity}.name') . " = ".$DB->sql_compare_text(':entityname') . " and "
        . $DB->sql_compare_text('{stg_app_startentities}.app_id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $this->id, 'entityname' => $entityname]);
        if ($id > 0) {
            return Entity::get_instance($id);
        } else {
            return null;
        }
    }

    public function get_startentities() {
        $startentities = [];
        global $DB;
        $sql = "select {stg_entity}.id from {stg_app_startentities} ".
        "left join {stg_entity} on {stg_app_startentities}.entity_id = {stg_entity}.id where "
        . $DB->sql_compare_text('{stg_app_startentities}.app_id') . " = ".$DB->sql_compare_text(':id');
        $ids = $DB->get_fieldset_sql($sql, ['id' => $this->id]);
        foreach ($ids as $id) {
            array_push($startentities, Entity::get_instance($id));
        }
        return $startentities;
    }

    public function add_startentity(Entity_Interface $entity) {
        global $DB;
        $DB->insert_record('stg_app_startentities', [
            'app_id' => $this->id,
            'entity_id' => $entity->get_id(),
        ]);
    }

    public function add_startentity_from_id(int $entityid) {
        global $DB;
        $DB->insert_record('stg_app_startentities', [
            'app_id' => $this->id,
            'entity_id' => $entityid,
        ]);
    }

    public function set_actions(int $actions) {
        global $DB;
        $DB->set_field('stg_app', 'actions', $actions, ['id' => $this->id]);
    }

    public function get_player() {
        global $DB;
        $sql = "select player_id from {stg_app} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $this->id]);
        return Player_Character::get_instance($id);
    }
    public function get_current_location() {
        return $this->get_player()->get_current_location();
    }
    public function set_current_location(Location_Interface $currentlocation) {
        $this->get_player()->set_currentlocation($currentlocation);
    }

    public function do_action(string $actionname, bool $debug) {
        $ret = $this->get_current_location()->check_actions($actionname);
        if (!$debug) {
            $ret[1] = [];
        }
        return $ret;
    }

    public function set_player(Player_Character $player) {
        global $DB;
        $DB->set_field('stg_app', 'player_id', $player->get_id(), ['id' => $this->id]);
    }
    private function parse() {
        if ($this->get_language() == Language::FR) {
            $itemsrow = $this->get_row("OBJETS");
            $charactersrow = $this->get_row("PERSONNAGES");
            $locationsrow = $this->get_row("LIEUX");
            $interactiondefautrow = $this->get_row("interaction avec objet n'existant pas :");
            $fouillerdefautrow = $this->get_row("Fouiller par défaut :");
        } else {
            $itemsrow = $this->get_row("ITEMS");
            $charactersrow = $this->get_row("CHARACTERS");
            $locationsrow = $this->get_row("LOCATIONS");
            $interactiondefautrow = $this->get_row("Interaction with non-existent object");
            $fouillerdefautrow = $this->get_row("Search by default");
        }
        $this->create_items($itemsrow);
        $this->create_characters($charactersrow);
        $this->create_locations($locationsrow);
        $this->check_items_duplicates();
        $this->create_all_actions($locationsrow);
        $interactiondefaut = $this->create_action_defaut($interactiondefautrow);
        $fouillerdefaut = $this->create_action_defaut($fouillerdefautrow);

        global $DB;
        $sql = "select playerkeyword from {stg_app} where "
        . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        $playerkeyword = $DB->get_field_sql($sql, ['id' => $this->id]);
        $player = $this->get_startentity($playerkeyword);
        if ($player != null) {
            try {
                $character = Character::get_instance_from_parent_id($player->get_id());
                try {
                    $player = Player_Character::get_instance_from_parent_id($character->get_id());
                } catch (Exception $e) {
                    if ($this->get_language() == Language::FR) {
                        throw new Exception('Aucun joueur trouvé dans ce jeu');
                    } else {
                        throw new Exception('No player found in this game');
                    }
                }
            } catch (Exception $e) {
                if ($this->get_language() == Language::FR) {
                    throw new Exception('Aucun joueur trouvé dans ce jeu');
                } else {
                    throw new Exception('No player found in this game');
                }
            }
        }
        $arguments = [];
        if (isset($fouillerdefaut)) {
            $arguments = array_merge($arguments, ['defaultactionsearch' => $fouillerdefaut]);
        }
        if (isset($interactiondefaut)) {
            $arguments = array_merge($arguments, ['defaultactioninteract' => $interactiondefaut]);
        }
        $DB->update_record('stg_app', [
            'id' => $this->id,
            'player_id' => $player->get_id(),
            'defaultactionsearch_id' => $fouillerdefaut->get_id(),
            'defaultactioninteract_id' => $interactiondefaut->get_id(),
        ]);
        new Game(null, [], $this->get_startentities());
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
                try {
                    new Item(null, $description, $name, $statuses);
                } catch (Exception $e) {
                    throw new Cell_Exception($e->getMessage(), $row, $col);
                }
            }
            $col++;
        }
    }

    private function create_characters($row) {
        $col = 1;
        global $DB;
        $sql = "select playerkeyword from {stg_app} where "
        . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        $playerkeyword = $DB->get_field_sql($sql, ['id' => $this->id]);
        $nplayers = 0;
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
                        if ($this->get_language() == Language::FR) {
                            $errormessage = $itemname . " n'est pas un objet";
                            throw new Cell_Exception($errormessage, $row + 3, $col);
                        } else {
                            $errormessage = $itemname . " is not an item";
                            throw new Cell_Exception($errormessage, $row + 3, $col);

                        }
                    }
                } else {
                    if ($this->get_language() == Language::FR) {
                            throw new Exception($itemname . " n'est pas un objet avec la ligne: " . $row . " et
                             la colonne: " . $col ."");
                    } else {
                        throw new Exception($itemname . " is not an item with the row: " . $row . " and the col: " . $col ."");
                    }
                }
                array_push($items, $item);
            }
            if ($name == $playerkeyword) {
                new Player_Character(null, $description, $name, $statuses, $items, null);
                $nplayers++;
            } else {
                try {
                    new Npc_Character(null, $description, $name, $statuses, $items, null);
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
                if ($item != null) {
                    try {
                        $item = Item::get_instance_from_parent_id($item->get_id());
                    } catch (Exception $e) {
                        if ($this->get_language() == Language::FR) {
                            throw new Exception($itemname . " n'est pas un objet avec la ligne: " . $row . "
                            et la colonne: " . $col ."");
                        } else {
                            throw new Exception($itemname . "is not an item and here is the row: "
                            . $row . " and the col: " . $col ."");
                        }
                    }
                } else {
                    if ($this->get_language() == Language::FR) {
                        $errormessage = $itemname . " n'est pas un objet";
                        throw new Cell_Exception($errormessage, $row + 2, $col);
                    } else {
                        $errormessage = $itemname . "is not an item";
                        throw new Cell_Exception($errormessage, $row + 2, $col);

                    }
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
            try {
                new Location(null, $name, $statuses, $items, $hints, [], 0);
            } catch (Exception $e) {
                throw new Cell_Exception($e->getMessage(), $row, $col);
            }
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
                    if ($this->get_language() == Language::FR) {
                        throw new Exception($locationname . " n'est pas un lieu");
                    } else {
                        throw new Exception($locationname . "is not a location");
                    }
                }
            } else {
                if ($this->get_language() == Language::FR) {
                    $errormessage = $locationname . " n'est pas un lieu";
                    throw new Cell_Exception($errormessage, $row, $col);
                } else {
                    $errormessage = $locationname . " is not a location";
                    throw new Cell_Exception($errormessage, $row, $col);
                }
            }
            $characternames = $this->get_cell_array_string($row + 3, $col);
            foreach ($characternames as $name) {
                $character = $this->get_startentity($name);
                if ($character != null) {
                    try {
                        $character = Character::get_instance_from_parent_id($character->get_id());
                    } catch (Exception $e) {
                        if ($this->get_language() == Language::FR) {
                            throw new Exception($name . " n'est pas un personnage");
                        } else {
                            throw new Exception($name . " is not a character");
                        }
                    }
                } else {
                    if ($this->get_language() == Language::FR) {
                        $errormessage = $name . " n'est pas un personnage";
                        throw new Cell_Exception($errormessage, $row + 3, $col);
                    } else {
                        $errormessage = $name . " is not a character";
                        throw new Cell_Exception($errormessage, $row + 3, $col);
                    }
                }
                $character->set_currentlocation($location);
            }
            $col++;
        }
    }

    private function check_items_duplicates() {
        $itemsentities = [];
        foreach ($this->get_startentities() as $e) {
            if ($e instanceof Item) {
                if (in_array($e->get_name(), $itemsentities)) {
                    if ($this->get_language() == Language::FR) {
                        throw new Exception("Il y a un doublon d'objet: " . $e->get_name());
                    } else {
                        throw new Exception("There is a duplicate item: " . $e->get_name());
                    }
                } else {
                    array_push($itemsentities, $e->get_name());
                }
            }
        }
    }

    private function create_all_actions($row) {
        $col = 1;
        while (array_key_exists($col, $this->csvdata[$row]) && $this->csvdata[$row][$col] != null) {
            $locationname = $this->get_cell_string($row, $col);
            $location = $this->get_startentity($locationname);
            if ($location != null) {
                try {
                    $location = Location::get_instance_from_parent_id($location->get_id());
                } catch (Exception $e) {
                    if ($this->get_language() == Language::FR) {
                        $errormessage = $locationname . " n'est pas un lieu";
                        throw new Cell_Exception($errormessage, $row, $col);
                    } else {
                        $errormessage = $locationname . " is not a location";
                        throw new Cell_Exception($errormessage, $row, $col);
                    }
                }
            } else {
                if ($this->get_language() == Language::FR) {
                    $errormessage = $locationname . " n'est pas un lieu";
                    throw new Cell_Exception($errormessage, $row, $col);
                } else {
                    $errormessage = $locationname . " is not a location";
                    throw new Cell_Exception($errormessage, $row, $col);
                }
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
                    if ($this->get_language() == Language::FR) {
                        $errormessage = $entityname . " n'est pas une entité";
                        throw new Cell_Exception($errormessage, $row + 3, $col);
                    } else {
                        $errormessage = $entityname . " is not an entity";
                        throw new Cell_Exception($errormessage, $row + 3, $col);
                    }
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
                            if ($this->get_language() == Language::FR) {
                                $errormessage = $name . " n'est pas un objet";
                                throw new Cell_Exception($errormessage, $row + 6, $col);
                            } else {
                                $errormessage = $name . " is not an item";
                                throw new Cell_Exception($errormessage, $row + 6, $col);
                            }
                        }
                    } else {
                        if ($this->get_language() == Language::FR) {
                            $errormessage = $name . " n'est pas un objet";
                            throw new Cell_Exception($errormessage, $row + 6, $col);
                        } else {
                            $errormessage = $name . " is not an item";
                            throw new Cell_Exception($errormessage, $row + 6, $col);
                        }
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
                            if ($this->get_language() == Language::FR) {
                                $errormessage = $name . " n'est pas un objet";
                                throw new Cell_Exception($errormessage, $row + 7, $col);
                            } else {
                                $errormessage = $name . " is not an item";
                                throw new Cell_Exception($errormessage, $row + 7, $col);
                            }
                        }
                    } else {
                        if ($this->get_language() == Language::FR) {
                            $errormessage = $name . " n'est pas un objet";
                            throw new Cell_Exception($errormessage, $row + 7, $col);
                        } else {
                            $errormessage = $name . " is not an item";
                            throw new Cell_Exception($errormessage, $row + 7, $col);
                        }
                    }
                    array_push($olditems, $item);
                }
            }
            if ($entity != null) {
                $parententity = $entity;
                try {
                    $entity = Location::get_instance_from_parent_id($parententity->get_id());
                } catch (Exception $e) {
                    try {
                        $entity = Character::get_instance_from_parent_id($parententity->get_id());
                    } catch (Exception $e) {
                        $e;
                    }
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
                            if ($this->get_language() == Language::FR) {
                                throw new Exception($locationname . " n'est pas un lieu");
                            } else {
                                throw new Exception($locationname . " is not a location");
                            }
                        }
                    } else {
                        if ($this->get_language() == Language::FR) {
                            throw new Exception($locationname . " n'est pas un lieu");
                        } else {
                            throw new Exception($locationname . " is not a location");
                        }
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
                if ($this->get_language() == Language::FR) {
                    throw new Exception($entityname . " n'est pas une entité");
                } else {
                    throw new Exception("Only characters and locations can have reactions");
                }
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
            if ($this->get_language() == Language::FR) {
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
                null,
                Condition::get_instance($this->read_tree($tree[1], $reactions)->get_parent_id()),
                Condition::get_instance($this->read_tree($tree[0], $reactions)->get_parent_id()),
                $tree[2],
                $reactions
            );
        } else {
            if ($this->get_language() == Language::FR) {
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
            if ($this->get_language() == Language::FR) {
                throw new Exception("Syntaxe de condition incorrecte : " . $condition);
            } else {
                throw new Exception("Wrong condition syntax : " . $condition);
            }
        }

        $connector .= $tokens[$connectorstart];
        if ($this->get_language() == Language::FR) {
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
            if ($this->get_language() == Language::FR) {
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
    public function delete_all_data() {
        global $DB;
        $tablestoclear = [
            'stg_language',
            'stg_app',
            'stg_app_startentities',
            'stg_game',
            'stg_game_visitedlocations',
            'stg_game_entities',
            'stg_entity',
            'stg_entity_status',
            'stg_location',
            'stg_location_hints',
            'stg_location_actions',
            'stg_hint',
            'stg_action',
            'stg_action_conditions',
            'stg_defaultaction',
            'stg_condition',
            'stg_condition_reactions',
            'stg_nodecondition',
            'stg_leafcondition',
            'stg_leafcondition_status',
            'stg_reaction',
            'stg_reaction_oldstatus',
            'stg_reaction_newstatus',
            'stg_reaction_olditems',
            'stg_reaction_newitems',
            'stg_noentityreaction',
            'stg_locationreaction',
            'stg_characterreaction',
            'stg_character',
            'stg_npccharacter',
            'stg_playercharacter',
            'stg_inventory',
            'stg_inventory_items',
            'stg_item',
        ];
        foreach ($tablestoclear as $table) {
            $DB->delete_records($table);
        }
    }

    public function restart_game_from_start(int $deaths, DateTime $starttimes, array $visitedlocations, int $actions) {
        $csvfilepath = $this->get_csvfilepath();
        $this->delete_all_data();
        new App(null, $csvfilepath, $deaths, $actions, $starttimes, $visitedlocations);
    }

    public function get_id() {
        return $this->id;
    }
    public function get_save() {
        return null;
    }
    public function restart_game_from_save() {
        return null;
    }

}
