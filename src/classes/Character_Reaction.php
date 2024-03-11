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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Reaction.php');

/**
 * Class Character_Reaction
 * @package mod_serioustextualgame
 */
class Character_Reaction extends Reaction {

    private int $id;

    public function __construct(?int $id, string $description, array $oldstatus, array $newstatus,
    array $olditem, array $newitem, ?Character_Interface $character, ?Location_Interface $newlocation) {
        global $DB;
        if (!isset($id)) {
            parent::__construct(null, $description, $oldstatus, $newstatus, $olditem, $newitem);
            $arguments = [
                'reaction_id' => parent::get_id(),
                'character_id' => $character->get_id(),
            ];
            if (isset($newlocation)) {
                $arguments['newlocation_id'] = $newlocation->get_id();
            }
            $this->id = $DB->insert_record('characterreaction', $arguments);
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {characterreaction} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Character_Reaction object of ID:".$id." exists.");
            }
            $sql = "select reaction_id from {characterreaction} where "
            . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
            $super = $DB->get_field_sql($sql, ['id' => $id]);
            parent::__construct($super, "", [], [], [], []);
            $this->id = $id;
        }
    }

    public function get_parent_id() {
        return parent::get_id();
    }

    public static function get_instance_from_parent_id(int $reactionid): Character_Reaction {
        global $DB;
        $sql = "select id from {characterreaction} where "
        . $DB->sql_compare_text('reaction_id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $reactionid]);
        return self::get_instance($id);
    }

    public static function get_instance(int $id): Character_Reaction {
        return new Character_Reaction($id, "", [], [], [], [], null, null);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_character(): Character {
        global $DB;
        $sql = "select character_id from {characterreaction} where "
        . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return Character::get_instance($DB->get_field_sql($sql, ['id' => $this->id]));
    }

    public function get_new_location() {
        global $DB;
        $sql = "select newlocation_id from {characterreaction} where ". $DB->sql_compare_text('id')
        . " = ".$DB->sql_compare_text(':id');
        $newlocationid = $DB->get_field_sql($sql, ['id' => $this->id]);
        if ($newlocationid > 0) {
            return Location::get_instance($newlocationid);
        } else {
            return null;
        }
    }

    public function set_new_location(Location_Interface $newlocation) {
        global $DB;
        $DB->set_field('characterreaction', 'newlocation_id', $newlocation->get_id(), ['id' => $this->id]);
    }

    public function do_reactions(): array {
        global $DB;
        $app = App::get_instance();
        $game = $app->get_game();
        $character = $this->get_character();
        $parentreaction = Reaction::get_instance($this->get_parent_id());
        $return = [];
        if (isset($character)) {
            $newlocation = $this->get_new_location();
            if ($newlocation != null) {
                try {
                    $npccharacter = Npc_Character::get_instance_from_parent_id($character->get_id());
                    $npccharacter->set_currentlocation($newlocation);
                } catch (Exception $e) {
                    try {
                        $playercharacter = Player_Character::get_instance_from_parent_id($character->get_id());
                        $game->set_current_location($newlocation);
                        $game->add_visited_location($newlocation);
                        array_push($return, $game->do_action("description", false)[0]);
                    } catch (Exception $e) {
                        $e;
                    }
                }
            }
            $newitems = $parentreaction->get_new_item();
            if ($newitems != null) {
                foreach ($newitems as $item) {
                    $character->get_inventory()->add_item($item);
                }
            }
            $olditem = $parentreaction->get_old_item();
            if ($olditem != null) {
                foreach ($olditem as $item) {
                    $character->get_inventory()->remove_item($item);
                }
            }
            $newstatus = $parentreaction->get_new_status();
            if ($newstatus != null) {
                $character->add_status($newstatus);
                try {
                    $playercharacter = Player_Character::get_instance_from_parent_id($character->get_id());
                    foreach ($newstatus as $status) {
                        if ($status == "mort") {
                            $game->add_deaths();
                            $app->restart_game_from_start();
                            array_push($return, "Vous avez échoué. Vous recommencez.");
                        }
                        if ($status == "victoire") {
                            $deaths = $game->get_deaths();
                            $starttime = $game->get_start_time();
                            $endtime = new DateTime();
                            $interval = $starttime->diff($endtime);
                            $time = $interval->format('%H:%I:%S');
                            $lieux = $game->get_visited_locations();
                            array_push($return, "Vous avez gagné en " . $time . " avec " . $deaths
                            . " morts et " . count($lieux) . " lieux visités.");
                        }
                    }
                } catch (Exception $e) {
                    $e;
                }
            }
            $oldstatus = $parentreaction->get_old_status();
            if ($oldstatus != null) {
                $character->remove_status($oldstatus);
            }
        }
        return $return;
    }
}
