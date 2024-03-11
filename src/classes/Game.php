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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Game_Interface.php');

class Game implements Game_Interface {

    private int $id;

    public function __construct(?int $id, int $deaths, int $actions, array $visitedlocations, ?DateTime $starttime,
    ?Player_Character $player, ?Default_Action_Interface $defaultactionsearch, ?Default_Action_Interface $defaultactioninteract,
    array $entities) {
        global $DB;
        if (!isset($id)) {
            $app = App::get_instance();
            Util::check_array($visitedlocations, Location_Interface::class);
            Util::check_array($entities, Entity_Interface::class);
            $this->id = $DB->insert_record('game', [
                'deaths' => $deaths,
                'actions' => $actions,
                'starttime' => $starttime->getTimestamp(),
                'player_id' => $player->get_id(),
                'defaultactionsearch_id' => $defaultactionsearch->get_id(),
                'defaultactioninteract_id' => $defaultactioninteract->get_id(),
            ]);
            $app->set_game($this);
            foreach ($visitedlocations as $location) {
                $DB->insert_record('game_visitedlocations', [
                    'game_id' => $this->id,
                    'location_id' => $location->get_id(),
                ]);
            }
            foreach ($entities as $entity) {
                $DB->insert_record('game_entities', [
                    'game_id' => $this->id,
                    'entity_id' => $entity->get_id(),
                ]);
            }
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {game} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Game object of ID:".$id." exists.");
            }
            $this->id = $id;
        }
    }

    public static function get_instance(int $id) {
        return new Game($id, 0, 0, [], null, null, null, null, []);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_deaths() {
        global $DB;
        $sql = "select deaths from {game} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->id]);
    }
    public function add_deaths() {
        global $DB;
        $DB->set_field('game', 'deaths', $this->get_deaths() + 1, ['id' => $this->id]);
    }

    public function get_actions() {
        global $DB;
        $sql = "select actions from {game} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->id]);
    }
    public function set_actions(int $actions) {
        global $DB;
        $DB->set_field('game', 'actions', $actions, ['id' => $this->id]);
    }

    public function add_action() {
        global $DB;
        $DB->set_field('game', 'actions', $this->get_actions() + 1, ['id' => $this->id]);
    }

    public function get_player() {
        global $DB;
        $sql = "select player_id from {game} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $this->id]);
        return Player_Character::get_instance($id);
    }

    public function set_player(Player_Character $player) {
        global $DB;
        $DB->set_field('game', 'player_id', $player->get_id(), ['id' => $this->id]);
    }

    public function get_visited_locations() {
        $visitedlocations = [];
        global $DB;
        $sql = "select location_id from {game_visitedlocations} where "
        . $DB->sql_compare_text('game_id') . " = ".$DB->sql_compare_text(':id');
        $ids = $DB->get_fieldset_sql($sql, ['id' => $this->id]);
        foreach ($ids as $id) {
            array_push($visitedlocations, Location::get_instance($id));
        }
        return $visitedlocations;
    }
    public function set_visited_locations(array $visitedlocations) {
        $visitedlocations = Util::clean_array($visitedlocations, Location_Interface::class);
        global $DB;
        $DB->delete_records('game_visitedlocations', ['game_id' => $this->id]);
        foreach ($visitedlocations as $location) {
            $DB->insert_record('game_visitedlocations', [
                'game_id' => $this->id,
                'location_id' => $location->get_id(),
            ]);
        }
    }

    public function add_visited_location(Location_Interface $location) {
        $locations = $this->get_visited_locations();
        array_push($locations, $location);
        $this->set_visited_locations($locations);
    }

    public function get_start_time() {
        global $DB;
        $sql = "select starttime from {game} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        $datetime = new DateTime();
        $datetime->setTimestamp($DB->get_field_sql($sql, ['id' => $this->id]));
        return $datetime;
    }
    public function set_start_time(DateTime $starttime) {
        global $DB;
        $DB->set_field('game', 'starttime', $starttime->getTimestamp(), ['id' => $this->id]);
    }

    public function get_current_location() {
        return $this->get_player()->get_current_location();
    }
    public function set_current_location(Location_Interface $currentlocation) {
        $this->get_player()->set_currentlocation($currentlocation);
    }

    public function get_default_action_search() {
        global $DB;
        $sql = "select defaultactionsearch_id from {game} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return Default_Action::get_instance($DB->get_field_sql($sql, ['id' => $this->id]));
    }

    public function set_default_action_search(Default_Action_Interface $action) {
        global $DB;
        $DB->set_field('game', 'defaultactionsearch_id', $action->get_id(), ['id' => $this->id]);
    }

    public function get_default_action_interact() {
        global $DB;
        $sql = "select defaultactioninteract_id from {game} where "
        . $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return Default_Action::get_instance($DB->get_field_sql($sql, ['id' => $this->id]));
    }

    public function set_default_action_interact(Default_Action_Interface $action) {
        global $DB;
        $DB->set_field('game', 'defaultactioninteract_id', $action->get_id(), ['id' => $this->id]);
    }

    public function get_entities() {
        $entities = [];
        global $DB;
        $sql = "select entity_id from {game_entities} where "
        . $DB->sql_compare_text('game_id') . " = ".$DB->sql_compare_text(':id');
        $ids = $DB->get_fieldset_sql($sql, ['id' => $this->id]);
        foreach ($ids as $id) {
            array_push($entities, Entity::get_instance($id));
        }
        return $entities;
    }

    public function set_entities(array $entities) {
        $entities = Util::clean_array($entities, Entity_Interface::class);
        global $DB;
        $DB->delete_records('game_entities', ['game_id' => $this->id]);
        foreach ($entities as $entity) {
            $DB->insert_record('game_entities', [
                'game_id' => $this->id,
                'location_id' => $entity->get_id(),
            ]);
        }
    }

    public function add_entity(Entity_Interface $entity) {
        $entities = $this->get_entities();
        array_push($entities, $entity);
        $this->set_entities($entities);
    }

    /**
     * @return ?Entity_Interface
     */
    public function get_entity(string $name) {
        global $DB;
        $sql = "select {entity}.id from {game_entities} left join {entity} "
        . "on {game_entities}.entity_id = {entity}.id where "
        . $DB->sql_compare_text('{entity}.name') . " = ".$DB->sql_compare_text(':entityname') . " and "
        . $DB->sql_compare_text('{game_entities}.game_id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $this->id, 'entityname' => $name]);
        if ($id > 0) {
            return Entity::get_instance($id);
        } else {
            return null;
        }
    }

    public function do_action(string $actionname, bool $debug) {
        $ret = $this->get_current_location()->check_actions($actionname);
        if (!$debug) {
            $ret[1] = [];
        }
        return $ret;
    }

}
