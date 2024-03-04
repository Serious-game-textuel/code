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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Location_Interface.php');
class Location extends Entity implements Location_Interface {

    private int $id;

    public function __construct(?int $id, string $name, array $status, array $items, array $hints, array $actions, int $hintscount=0) {
        if (!isset($id)) {
            Util::check_array($status, 'string');
            Util::check_array($items, Item_Interface::class);
            Util::check_array($actions, Action_Interface::class);
            $super = new Entity(null, "", $name, $status);
            global $DB;
            $inventory = new Inventory($items);
            $this->id = $DB->insert_record('game', [
                'entity' => $super->get_id(),
                'inventory' => $inventory->get_id(),
            ]);
            foreach ($hints as $hint) {
                $DB->insert_record('location_hints', [
                    'location' => $this->id,
                    'hint' => $hint->get_id(),
                ]);
            }
            foreach ($actions as $action) {
                $DB->insert_record('location_actions', [
                    'location' => $this->id,
                    'action' => $action->get_id(),
                ]);
            }
        } else {
            $this->id = $id;
        }
    }

    public static function get_instance(int $id) {
        return new Location($id, 0, 0, [], null, null, null, null, []);
    }

    public function get_inventory() {
        global $DB;
        $sql = "select inventory from {location} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return Inventory::get_instance($DB->get_field_sql($sql, ['id' => $this->get_id()]));
    }
    public function get_actions() {
        $actions = [];
        global $DB;
        $sql = "select action from {location_actions} where "
        . $DB->sql_compare_text('location') . " = ".$DB->sql_compare_text(':id');
        $ids = $DB->get_fieldset_sql($sql, ['id' => $this->get_id()]);
        foreach ($ids as $id) {
            array_push($actions, Action::get_instance($id));
        }
        return $actions;
    }
    public function set_actions(array $actions) {
        $actions = Util::clean_array($actions, Location_Interface::class);
        global $DB;
        $DB->delete_records('location_actions', ['location' => $this->get_id()]);
        foreach ($actions as $action) {
            $DB->insert_record('location_actions', [
                'location' => $this->id,
                'action' => $action->get_id(),
            ]);
        }
    }
    public function get_hints() {
        $hints = [];
        global $DB;
        $sql = "select hint from {location_hints} where "
        . $DB->sql_compare_text('location') . " = ".$DB->sql_compare_text(':id');
        $ids = $DB->get_fieldset_sql($sql, ['id' => $this->get_id()]);
        foreach ($ids as $id) {
            array_push($hints, Hint::get_instance($id));
        }
        return $hints;
    }

    public function is_action_valide(string $action) {
        $actions = $this->get_actions();
        for ($i = 0; $i < count($actions); $i++) {
            if ($actions[$i]->get_description() == $action) {
                return $actions[$i];
            }
        }
        return null;
    }

    public function check_actions(string $action) {
        $return = [];
        $app = App::get_instance();
        $game = $app->get_game();
        $action = App::tokenize($action);
        $actionvalide = $this->is_action_valide($action);
        if ($actionvalide != null) {
            $result = $actionvalide->do_conditions();
            foreach ($result as $res) {
                array_push($return, $res);
            }
        } else {
            $defaultaction = "fouiller";
            if (strpos($action, $defaultaction) === 0) {
                $entity = substr($action, strlen($defaultaction) + 1);
                if ($game->get_entity($entity) !== null) {
                    if ($game->get_default_action_interact() !== null) {
                        $result = $game->get_default_action_interact()->do_conditions_verb($defaultaction);
                        foreach ($result as $res) {
                            array_push($return, $res);
                        }
                    } else {
                        if ($game->get_default_action_search() !== null) {
                            $result = $game->get_default_action_search()->do_conditions_verb($defaultaction);
                            foreach ($result as $res) {
                                array_push($return, $res);
                            }
                        }
                    }
                } else {
                    if ($game->get_default_action_interact() !== null) {
                        $result = $game->get_default_action_interact()->do_conditions_verb($defaultaction);
                        foreach ($result as $res) {
                            array_push($return, $res);
                        }
                    } else {
                        array_push($return, "je n'ai pas compris ce que tu voulais ".$defaultaction);
                    }
                }
            } else if ($action == "indices") {
                $hints = $this->get_hints();
                $hintcount = $this->get_hintscount();
                if ($hintcount < count($hints)) {
                    $hint = $hints[$hintcount];
                    $this->increments_hintscount();
                    array_push($return, $hint->get_description());
                } else {
                    array_push($return, "Aucun autre indice disponible.");
                }
            } else if ($action == "sortie") {
                array_push($return, $this->get_exit());
            } else if ($action == "inventaire") {
                array_push($return, $this->get_inventory_description());
            } else {
                $firstword = explode(' ', $action)[0];
                if ($game->get_default_action_interact() !== null) {
                    $result = $game->get_default_action_interact()->do_conditions_verb($firstword);
                    foreach ($result as $res) {
                        array_push($return, $res);
                    }
                } else {
                    array_push($return, $action.'? Tu ne peux pas faire ca.');
                }
            }
        }
        return $return;
    }

    public function get_hintscount() {
        global $DB;
        $sql = "select hintscount from {location} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->get_id()]);
    }
    public function increments_hintscount() {
        global $DB;
        $DB->set_field('location', 'hintscount', $this->get_hintscount() + 1, ['id' => $this->get_id()]);
    }
    public function has_item_location(Item_Interface $item) {
        return $this->get_inventory()->check_item($item);
    }

    public function get_exit() {
        global $DB;
        $sortie = "Sorties disponibles : ";
        foreach ($this->get_actions() as $action) {
            $conditions = $action->get_conditions();
            foreach ($conditions as $condition) {
                $reactions = $condition->get_reactions();
                foreach ($reactions as $reaction) {
                    $ischaracterreaction = $DB->record_exists_sql(
                        "SELECT id FROM {characterreaction} WHERE ".$DB->sql_compare_text('reaction')." = ".$DB->sql_compare_text(':id'),
                        ['id' => $reaction->get_id()]
                    );
                    if ($ischaracterreaction) {
                        $isplayercharacter = $DB->record_exists_sql(
                            "SELECT id FROM {playercharacter} WHERE ".$DB->sql_compare_text('character')." = ".$DB->sql_compare_text(':id'),
                            ['id' => $reaction->get_character()->get_id()]
                        );
                        if ($reaction->get_new_location() != null && $isplayercharacter) {
                            $description = explode(" ", $action->get_description());
                            $sortie .= implode(' ', array_slice($description, 1)).", ";
                        }
                    }
                }
            }
        }
        return rtrim($sortie, " ,");
    }

    public function get_inventory_description() {
        $game = App::get_instance()->get_game();
        $player = $game->get_player();
        $inventory = $player->get_inventory();
        $items = $inventory->get_items();
        $description = "Inventaire : ";
        if (count($items) == 0) {
            $description .= "vide";
        } else {
            foreach ($items as $item) {
                $description .= $item->get_name().", ";
            }
        }
        return rtrim($description, " ,");
    }
}
