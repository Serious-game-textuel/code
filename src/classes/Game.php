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
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Game_Interface.php');

/**
 * Class Game
 * @package mod_stg
 */
class Game implements Game_Interface {

    private int $id;

    public function __construct(?int $id, array $visitedlocations, array $entities) {
        global $DB;
        if (!isset($id)) {
            $app = App::get_instance();
            Util::check_array($visitedlocations, Location_Interface::class);
            Util::check_array($entities, Entity_Interface::class);
            $this->id = $DB->insert_record('stg_game', [
                'filler' => "filler",
            ]);
            $app->set_game($this);
            foreach ($visitedlocations as $location) {
                $DB->insert_record('stg_game_visitedlocations', [
                    'game_id' => $this->id,
                    'location_id' => $location->get_id(),
                ]);
            }
            foreach ($entities as $entity) {
                $DB->insert_record('stg_game_entities', [
                    'game_id' => $this->id,
                    'entity_id' => $entity->get_id(),
                ]);
            }
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {stg_game} WHERE "
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
        return new Game($id, [], []);
    }

    public function get_id() {
        return $this->id;
    }


    public function get_entities() {
        $entities = [];
        global $DB;
        $sql = "select entity_id from {stg_game_entities} where "
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
        $DB->delete_records('stg_game_entities', ['game_id' => $this->id]);
        foreach ($entities as $entity) {
            $DB->insert_record('stg_game_entities', [
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
        $sql = "select {stg_entity}.id from {stg_game_entities} left join {stg_entity} "
        . "on {stg_game_entities}.entity_id = {stg_entity}.id where "
        . $DB->sql_compare_text('{stg_entity}.name') . " = ".$DB->sql_compare_text(':entityname') . " and "
        . $DB->sql_compare_text('{stg_game_entities}.game_id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $this->id, 'entityname' => $name]);
        if ($id > 0) {
            return Entity::get_instance($id);
        } else {
            return null;
        }
    }

}
