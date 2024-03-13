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
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Location_Interface.php');

/**
 * Class Location
 * @package mod_stg
 */
class Location extends Entity implements Location_Interface {

    private int $id;

    public function __construct(?int $id, string $name, array $status, array $items,
    array $hints, array $actions, int $hintscount=0) {
        global $DB;
        if (!isset($id)) {
            Util::check_array($items, Item_Interface::class);
            Util::check_array($actions, Action_Interface::class);
            parent::__construct(null, "", $name, $status);
            $inventory = new Inventory(null, $items);
            $this->id = $DB->insert_record('stg_location', [
                'entity_id' => parent::get_id(),
                'inventory_id' => $inventory->get_id(),
            ]);
            foreach ($hints as $hint) {
                $DB->insert_record('stg_location_hints', [
                    'location_id' => $this->id,
                    'hint_id' => $hint->get_id(),
                ]);
            }
            foreach ($actions as $action) {
                $DB->insert_record('stg_location_actions', [
                    'location_id' => $this->id,
                    'action_id' => $action->get_id(),
                ]);
            }
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {stg_location} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Location object of ID:".$id." exists.");
            }
            $sql = "select entity_id from {stg_location} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
            $super = $DB->get_field_sql($sql, ['id' => $id]);
            parent::__construct($super, "", "", []);
            $this->id = $id;
        }
    }

    public function get_parent_id() {
        return parent::get_id();
    }

    public static function get_instance_from_parent_id(int $entityid): Location {
        global $DB;
        $sql = "select id from {stg_location} where "
        . $DB->sql_compare_text('entity_id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $entityid]);
        return self::get_instance($id);
    }

    public static function get_instance(int $id): Location {
        return new Location($id, "", [], [], [], []);
    }

    public function get_inventory() {
        global $DB;
        $sql = "select inventory_id from {stg_location} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return Inventory::get_instance($DB->get_field_sql($sql, ['id' => $this->id]));
    }
    public function get_actions() {
        $actions = [];
        global $DB;
        $sql = "select action_id from {stg_location_actions} where "
        . $DB->sql_compare_text('location_id') . " = ".$DB->sql_compare_text(':id');
        $ids = $DB->get_fieldset_sql($sql, ['id' => $this->id]);
        foreach ($ids as $id) {
            array_push($actions, Action::get_instance($id));
        }
        return $actions;
    }
    public function set_actions(array $actions) {
        $actions = Util::clean_array($actions, Action_Interface::class);
        global $DB;
        $DB->delete_records('stg_location_actions', ['location_id' => $this->id]);
        foreach ($actions as $action) {
            $DB->insert_record('stg_location_actions', [
                'location_id' => $this->id,
                'action_id' => $action->get_id(),
            ]);
        }
    }
    public function get_hints() {
        $hints = [];
        global $DB;
        $sql = "select hint_id from {stg_location_hints} where "
        . $DB->sql_compare_text('location_id') . " = ".$DB->sql_compare_text(':id');
        $ids = $DB->get_fieldset_sql($sql, ['id' => $this->id]);
        foreach ($ids as $id) {
            array_push($hints, Hint::get_instance($id));
        }
        return $hints;
    }

    public function get_actions_valide(string $action) {
        $actions = $this->get_actions();
        $valid = [];
        for ($i = 0; $i < count($actions); $i++) {
            if ($actions[$i]->get_description() == $action) {
                array_push($valid, $actions[$i]);
            }
        }
        $actiontoken = explode(" ", $action);
        $app = App::get_instance();
        if ($app->get_language() == Language::FR) {
            $synonyms = Util::get_french_synonyms($actiontoken[0]);
        } else if ($app->get_language() == Language::EN) {
            $synonyms = Util::get_english_synonyms($actiontoken[0]);
        } else {
            return $valid;
        }
        for ($i = 0; $i < count($actions); $i++) {
            $description = explode(" ", $actions[$i]->get_description());
            $firstword = $description[0];
            foreach ($synonyms as $synonym) {
                if ($firstword == $synonym) {
                    for ($j = 1; $j < count($actiontoken); $j++) {
                        if ($description[$j] != $actiontoken[$j]) {
                            break;
                        }
                        if ($j == count($actiontoken) - 1) {
                            array_push($valid, $actions[$i]);
                        }
                    }
                }
            }
        }
        return $valid;
    }
    public function check_actions(string $actionname) {
        $return = [];
        $debug = [];
        $app = App::get_instance();
        $language = $app->get_language();
        $game = $app->get_game();
        $actionname = App::tokenize($actionname);
        $actionsvalide = $this->get_actions_valide($actionname);
        if (count($actionsvalide) > 0) {
            array_push($debug, count($actionsvalide).' action(s) trouvée(s)');
            foreach ($actionsvalide as $actionvalide) {
                $result = $actionvalide->do_conditions();
                foreach ($result[0] as $res) {
                    array_push($return, $res);
                }
                array_push($debug, implode(', ', $result[1]));
            }
        } else if ($language == "fr") {
            array_push($debug, '0 action trouvée');
            $defaultactionname = "fouiller";
            if (strpos($actionname, $defaultactionname) === 0) {
                $entityname = substr($actionname, strlen($defaultactionname) + 1);
                if ($game->get_entity($entityname) !== null) {
                    $defaultactionsearch = $app->get_default_action_search();
                    if ($defaultactionsearch !== null) {
                        array_push($debug, "utilisation de l'action fouiller par défaut");
                        $result = $defaultactionsearch->do_conditions_verb($defaultactionname);
                        $return = array_merge($result[0], $return);
                        array_push($debug, implode(', ', $result[1]));
                    } else {
                        array_push($return, "je n'ai pas compris ce que tu voulais ".$defaultactionname);
                    }
                } else {
                    $defaultactioninteract = $app->get_default_action_interact();
                    if ($defaultactioninteract !== null) {
                        array_push($debug, "utilisation de l'action intéragir par défaut");
                        $result = $defaultactioninteract->do_conditions_verb($defaultactionname);
                        $return = array_merge($result[0], $return);
                        array_push($debug, implode(', ', $result[1]));
                    } else {
                        array_push($return, "je n'ai pas compris ce que tu voulais ".$defaultactionname);
                    }
                }
            } else if ($actionname == "indices" || $actionname == "indice") {
                array_push($debug, "utilisation de l'action prédéfinie indice");
                $hints = $this->get_hints();
                $hintcount = $this->get_hintscount();
                if ($hintcount < count($hints)) {
                    $hint = $hints[$hintcount];
                    $this->increments_hintscount();
                    array_push($return, $hint->get_description());
                } else {
                    array_push($return, "Aucun autre indice disponible.");
                }
            } else if ($actionname == "sortie") {
                array_push($debug, "utilisation de l'action prédéfinie sorties");
                array_push($return, $this->get_exit());
            } else if ($actionname == "inventaire") {
                array_push($debug, "utilisation de l'action prédéfinie inventaire");
                array_push($return, $app->get_player()->get_inventory()->__toString());
            } else {
                array_push($debug, "utilisation de l'action intéragir par défaut");
                $firstword = explode(' ', $actionname)[0];
                $defaultactioninteract = $app->get_default_action_interact();
                if ($defaultactioninteract !== null) {
                    $result = $defaultactioninteract->do_conditions_verb($firstword);
                    $return = array_merge($result[0], $return);
                    array_push($debug, implode(', ', $result[1]));
                } else {
                    array_push($return, $actionname.'? Tu ne peux pas faire ca.');
                }
            }
        } else {
            array_push($debug, '0 action found');
            $defaultactionname = "search";
            if (strpos($actionname, $defaultactionname) === 0) {
                $entityname = substr($actionname, strlen($defaultactionname) + 1);
                if ($game->get_entity($entityname) !== null) {
                    $defaultactionsearch = $app->get_default_action_search();
                    if ($defaultactionsearch !== null) {
                        array_push($debug, "using default search action");
                        $result = $defaultactionsearch->do_conditions_verb($defaultactionname);
                        $return = array_merge($result[0], $return);
                        array_push($debug, implode(', ', $result[1]));
                    } else {
                        array_push($return, "I don't understand what you want to ".$defaultactionname);
                    }
                } else {
                    $defaultactioninteract = $app->get_default_action_interact();
                    if ($defaultactioninteract !== null) {
                        array_push($debug, "using default interact action");
                        $result = $defaultactioninteract->do_conditions_verb($defaultactionname);
                        $return = array_merge($result[0], $return);
                        array_push($debug, implode(', ', $result[1]));
                    } else {
                        array_push($return, "I don't understand what you want to ".$defaultactionname);
                    }
                }
            } else if ($actionname == "hints" || $actionname == "hint") {
                array_push($debug, "using default hint action");
                $hints = $this->get_hints();
                $hintcount = $this->get_hintscount();
                if ($hintcount < count($hints)) {
                    $hint = $hints[$hintcount];
                    $this->increments_hintscount();
                    array_push($return, $hint->get_description());
                } else {
                    array_push($return, "No more hints available.");
                }
            } else if ($actionname == "exit") {
                array_push($debug, "using default exit action");
                array_push($return, $this->get_exit());
            } else if ($actionname == "inventory") {
                array_push($debug, "using default inventory action");
                array_push($return, $app->get_player()->get_inventory()->__toString());
            } else {
                array_push($debug, "using default interact action");
                $firstword = explode(' ', $actionname)[0];
                $defaultactioninteract = $app->get_default_action_interact();
                if ($defaultactioninteract !== null) {
                    $result = $defaultactioninteract->do_conditions_verb($firstword);
                    $return = array_merge($result[0], $return);
                    array_push($debug, implode(', ', $result[1]));
                } else {
                    array_push($return, $actionname.'? You can\'t do that.');
                }
            }
        }
        return [$return, $debug];
    }

    public function get_hintscount() {
        global $DB;
        $sql = "select hintscount from {stg_location} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->id]);
    }
    public function increments_hintscount() {
        global $DB;
        $DB->set_field('stg_location', 'hintscount', $this->get_hintscount() + 1, ['id' => $this->id]);
    }
    public function has_item_location(Item_Interface $item) {
        return $this->get_inventory()->check_item($item);
    }

    public function get_exit() {
        global $DB;
        $app = App::get_instance();
        $language = $app->get_language();
        if ($language == "fr") {
            $sortie = "Sorties disponibles : ";
        } else {
            $sortie = "Exits available : ";
        }
        foreach ($this->get_actions() as $action) {
            $conditions = $action->get_conditions();
            foreach ($conditions as $condition) {
                $reactions = $condition->get_reactions();
                foreach ($reactions as $reaction) {
                    try {
                        $characterreaction = Character_Reaction::get_instance_from_parent_id($reaction->get_id());
                        $playercharacter = Player_Character::get_instance_from_parent_id(
                            $characterreaction->get_character()->get_id());
                        if ($characterreaction->get_new_location() != null) {
                            $description = explode(" ", $action->get_description());
                            $sortie .= implode(' ', array_slice($description, 1)).", ";
                        }
                    } catch (Exception $e) {
                        $e;
                    }
                }
            }
        }
        return rtrim($sortie, " ,");
    }

    public function get_id() {
        return $this->id;
    }
    public function get_inventory_description() {
        $app = App::get_instance();
        $language = $app->get_language();
        $game = $app->get_game();
        $player = $app->get_player();
        $inventory = $player->get_inventory();
        $items = $inventory->get_items();
        if ($language == "fr") {
            $description = "Inventaire : ";
        } else {
            $description = "Inventory : ";
        }
        if (count($items) == 0) {
            if ($language == "fr") {
                $description .= "vide";
            } else {
                $description .= "empty";
            }
        } else {
            foreach ($items as $item) {
                $description .= $item->get_name().", ";
            }
        }
        return rtrim($description, " ,");
    }
}
