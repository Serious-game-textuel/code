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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Entity_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Util.php');
class Entity implements Entity_Interface {

    private int $id;

    public function __construct(?int $id, string $description, string $name, array $status) {
        global $DB;
        if (!isset($id)) {
            $app = App::get_instance();
            Util::check_array($status, 'string');
            if ($app->get_startentity($name) != null) {
                throw new InvalidArgumentException("Each entity name must be unique : ".$name);
            }
            $this->id = $DB->insert_record('entity', [
                'description' => $description,
                'name' => $name,
            ]);
            foreach ($status as $statut) {
                $DB->insert_record('entity_status', [
                    'entity_id' => $this->id,
                    'status' => $statut,
                ]);
            }
            $app->add_startentity_from_id($this->id);
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {entity} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Entity object of ID:".$id." exists.");
            }
            $this->id = $id;
        }
    }

    public static function get_instance(int $id) {
        return new Entity($id, "", "", []);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_description() {
        global $DB;
        $sql = "select description from {entity} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->get_id()]);
    }

    public function set_description(string $description) {
        global $DB;
        $DB->set_field('entity', 'description', $description, ['id' => $this->get_id()]);
    }

    public function get_name() {
        global $DB;
        $sql = "select name from {entity} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->get_id()]);
    }

    public function set_name(string $name) {
        global $DB;
        $DB->set_field('entity', 'name', $name, ['id' => $this->get_id()]);
    }

    public function get_status() {
        $statusarray = [];
        global $DB;
        $sql = "select status from {entity_status} where "
        . $DB->sql_compare_text('entity_id') . " = ".$DB->sql_compare_text(':id');
        $status = $DB->get_fieldset_sql($sql, ['id' => $this->get_id()]);
        foreach ($status as $statut) {
            array_push($statusarray, $statut);
        }
        return $statusarray;
    }

    public function set_status(array $status) {
        $status = Util::clean_array($status, Location_Interface::class);
        global $DB;
        $DB->delete_records('entity_status', ['entity_id' => $this->get_id()]);
        foreach ($status as $statut) {
            $DB->insert_record('entity_status', [
                'entity_id' => $this->id,
                'status' => $statut,
            ]);
        }
    }

    public function add_status(array $status) {
        $statut = $this->get_status();
        array_push($statut, $status);
        $this->set_status($statut);
    }

    public function remove_status(array $status) {
        global $DB;
        $DB->delete_records('entity_status', ['entity' => $this->get_id(), 'status' => $status]);
    }
}
