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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Condition_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/class/Character_Reaction.php');

class Condition implements Condition_Interface {

    private int $id;

    public function __construct(?int $id, array $reactions) {
        if (!isset($id)) {
            global $DB;
            Util::check_array($reactions, Reaction_Interface::class);
            $this->id = $DB->insert_record('condition', []);
            foreach ($reactions as $reaction) {
                $DB->insert_record('condition_reactions', [
                    'condition' => $this->id,
                    'reaction' => $reaction->get_id(),
                ]);
            }
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {condition} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Condition object of ID:".$id." exists.");
            }
            $this->id = $id;
        }
    }

    public static function get_instance(int $id) {
        return new Condition($id, []);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_reactions() {
        $reactions = [];
        global $DB;
        $sql = "select location from {condition_reactions} where "
        . $DB->sql_compare_text('condition') . " = ".$DB->sql_compare_text(':id');
        $ids = $DB->get_fieldset_sql($sql, ['id' => $this->get_id()]);
        foreach ($ids as $id) {
            array_push($reactions, Reaction::get_instance($id));
        }
        return $reactions;
    }

    public function set_reactions(array $reactions) {
        $reactions = Util::clean_array($reactions, Reaction_Interface::class);
        global $DB;
        $DB->delete_records('condition_reactions', ['game' => $this->get_id()]);
        foreach ($reactions as $reaction) {
            $DB->insert_record('condition_reactions', [
                'condition' => $this->id,
                'reaction' => $reaction->get_id(),
            ]);
        }
    }

    public function do_reactions() {
        $app = App::get_instance();
        $game = $app->get_game();
        $reactions = $this->get_reactions();
        $descriptions = [];
        global $DB;
        foreach ($reactions as $reaction) {
            $ischaracterreaction = $DB->record_exists_sql(
                "SELECT id FROM {characterreaction} WHERE "
                .$DB->sql_compare_text('reaction')." = ".$DB->sql_compare_text(':id'),
                ['id' => $reaction->get_id()]
            );
            $islocationreaction = $DB->record_exists_sql(
                "SELECT id FROM {locationreaction} WHERE "
                .$DB->sql_compare_text('reaction')." = ".$DB->sql_compare_text(':id'),
                ['id' => $reaction->get_id()]
            );
            if ($ischaracterreaction) {
                $sql = "select id from {characterreaction} where ". $DB->sql_compare_text('reaction') . " = ".$DB->sql_compare_text(':id');
                $id = $DB->get_field_sql($sql, ['id' => $reaction->get_id()]);
                $reaction = Character_Reaction::get_instance($id);
                if ($reaction->get_character() != null) {
                    $character = $reaction->get_character();

                    if ($reaction->get_new_location() != null) {
                        $newlocation = $reaction->get_new_location();
                        $isnpccharacter = $DB->record_exists_sql(
                            "SELECT id FROM {npccharacter} WHERE "
                            .$DB->sql_compare_text('character')." = ".$DB->sql_compare_text(':id'),
                            ['id' => $character->get_id()]
                        );
                        $isplayercharacter = $DB->record_exists_sql(
                            "SELECT id FROM {playercharacter} WHERE "
                            .$DB->sql_compare_text('character')." = ".$DB->sql_compare_text(':id'),
                            ['id' => $character->get_id()]
                        );
                        if ($isnpccharacter) {
                            $character->set_currentlocation($newlocation);
                        } else if ($isplayercharacter) {
                            $game->set_current_location($newlocation);
                            $game->add_visited_location($newlocation);
                            $descriptionreturn = $newlocation->check_actions("description");
                        }
                    }
                    if ($reaction->get_new_item() != null) {
                        $newitems = $reaction->get_new_item();
                        foreach ($newitems as $item) {
                            $character->get_inventory()->add_item($item);
                        }
                    }
                    if ($reaction->get_old_item() != null) {
                        $olditem = $reaction->get_old_item();
                        foreach ($olditem as $item) {
                            $character->get_inventory()->remove_item($item);
                        }
                    }
                    if ($reaction->get_new_status() != null) {
                        $newstatus = $reaction->get_new_status();
                        $character->add_status($newstatus);
                        $isplayercharacter = $DB->record_exists_sql(
                            "SELECT id FROM {playercharacter} WHERE "
                            .$DB->sql_compare_text('character')." = ".$DB->sql_compare_text(':id'),
                            ['id' => $character->get_id()]
                        );
                        if ($isplayercharacter) {
                            foreach ($newstatus as $status) {
                                if ($status == "mort") {
                                    $game->add_deaths();
                                    echo "playermort recommmencer au début(pas implémenter)\n";
                                }
                            }
                            if ($newstatus == "victoire") {
                                $deaths = $game->get_deaths();
                                $starttime = $game->get_start_time();
                                $endtime = new DateTime();
                                $interval = $starttime->diff($endtime);
                                $time = $interval->format('%H:%I:%S');
                                $lieux = $game->get_visited_locations();
                                return "Vous avez gagné en " . $time . " avec " . $deaths
                                . " morts et " . count($lieux) . " lieux visités.";
                            }
                        }
                    }
                    if ($reaction->get_old_status() != null) {
                        $oldstatus = $reaction->get_old_status();
                        $character->remove_status($oldstatus);
                    }
                }
            } else if ($islocationreaction) {
                $sql = "select id from {locationreaction} where ". $DB->sql_compare_text('reaction') . " = ".$DB->sql_compare_text(':id');
                $id = $DB->get_field_sql($sql, ['id' => $reaction->get_id()]);
                $reaction = Location_Reaction::get_instance($id);
                if ($reaction->get_location() != null) {
                    $location = $reaction->get_location();
                    if ($reaction->get_new_status() != null) {
                        $newstatus = $reaction->get_new_status();
                        $location->add_status($newstatus);
                    }
                    if ($reaction->get_old_status() != null) {
                        $oldstatus = $reaction->get_old_status();
                        $location->remove_status($oldstatus);
                    }
                    if ($reaction->get_new_item() != null) {
                        $newitems = $reaction->get_new_item();
                        foreach ($newitems as $item) {
                            $location->get_inventory()->add_item($item);
                        }
                    }
                    if ($reaction->get_old_item() != null) {
                        $olditem = $reaction->get_old_item();
                        foreach ($olditem as $item) {
                            $location->get_inventory()->remove_item($item);
                        }
                    }
                }
            }
            array_push($descriptions, $reaction->get_description());
        }
        if (empty($descriptions)) {
            return "pas de réaction";
        }
        if (isset($descriptionreturn[0])) {
            array_push($descriptions, $descriptionreturn[0]);
        }
        return implode(' / ', $descriptions);
    }

    public function is_true() {
        global $DB;
        $isnodecondtion = $DB->record_exists_sql(
            "SELECT id FROM {nodecondition} WHERE "
            .$DB->sql_compare_text('condition')." = ".$DB->sql_compare_text(':id'),
            ['id' => $this->get_id()]
        );
        $isleafcondition = $DB->record_exists_sql(
            "SELECT id FROM {leafcondition} WHERE "
            .$DB->sql_compare_text('condition')." = ".$DB->sql_compare_text(':id'),
            ['id' => $this->get_id()]
        );
        if ($isnodecondtion) {
            $sql = "select id from {nodecondition} where ". $DB->sql_compare_text('condition') . " = ".$DB->sql_compare_text(':id');
            $idnodecondition = $DB->get_field_sql($sql, ['id' => $this->get_id()]);
            $nodecondition = Node_Condition::get_instance($idnodecondition);
            return $nodecondition->is_true();
        } else if ($isleafcondition) {
            $sql = "select id from {leafcondition} where ". $DB->sql_compare_text('condition') . " = ".$DB->sql_compare_text(':id');
            $idnodecondition = $DB->get_field_sql($sql, ['id' => $this->get_id()]);
            $nodecondition = Leaf_Condition::get_instance($idnodecondition);
            return $nodecondition->is_true();
        }
        return false;
    }

}
