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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Item_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Entity.php');

/**
 * Class Item
 * @package mod_serioustextualgame
 */
class Item extends Entity implements Item_Interface {

    private int $id;

    public function __construct(?int $id, string $description, string $name, array $status) {
        global $DB;
        if (!isset($id)) {
            parent::__construct(null, $description, $name, $status);
            $this->id = $DB->insert_record('item', [
                'entity_id' => parent::get_id(),
            ]);
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {item} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Item object of ID:".$id." exists.");
            }
            $sql = "select entity_id from {item} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
            $super = $DB->get_field_sql($sql, ['id' => $id]);
            parent::__construct($super, "", "", []);
            $this->id = $id;
        }
    }

    public function get_parent_id() {
        return parent::get_id();
    }

    public static function get_instance_from_parent_id(int $entityid) {
        global $DB;
        $sql = "select id from {item} where "
        . $DB->sql_compare_text('entity_id') . " = ".$DB->sql_compare_text(':id');
        $id = $DB->get_field_sql($sql, ['id' => $entityid]);
        return self::get_instance($id);
    }

    public static function get_instance(int $id) {
        return new Item($id, "", "", []);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_inventory() {
        return null;
    }

}
