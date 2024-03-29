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
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Reaction_Interface.php');


/**
 * Class Reaction
 * @package mod_stg
 * @copyright   2024 Paul Grandhomme, Loric Gallier, Benjamin Bracquier, Mathis Courant
 */
class Reaction implements Reaction_Interface {

    private int $id;

    public function __construct(?int $id, string $description, array $oldstatus, array $newstatus,
    array $olditem, array $newitem) {
        global $DB;
        if (!isset($id)) {
            Util::check_array($oldstatus, 'string');
            Util::check_array($newstatus, 'string');
            Util::check_array($olditem, Item_Interface::class);
            Util::check_array($newitem, Item_Interface::class);
            $this->id = $DB->insert_record('stg_reaction', [
                'description' => $description,
            ]);
            foreach ($oldstatus as $oldstatut) {
                $DB->insert_record('stg_reaction_oldstatus', [
                    'reaction_id' => $this->id,
                    'status' => $oldstatut,
                ]);
            }
            foreach ($newstatus as $newstatut) {
                $DB->insert_record('stg_reaction_newstatus', [
                    'reaction_id' => $this->id,
                    'status' => $newstatut,
                ]);
            }
            foreach ($olditem as $item) {
                $DB->insert_record('stg_reaction_olditems', [
                    'reaction_id' => $this->id,
                    'item_id' => $item->get_id(),
                ]);
            }
            foreach ($newitem as $item) {
                $DB->insert_record('stg_reaction_newitems', [
                    'reaction_id' => $this->id,
                    'item_id' => $item->get_id(),
                ]);
            }
        } else {
            $exists = $DB->record_exists_sql(
                "SELECT id FROM {stg_reaction} WHERE "
                .$DB->sql_compare_text('id')." = ".$DB->sql_compare_text(':id'),
                ['id' => $id]
            );
            if (!$exists) {
                throw new InvalidArgumentException("No Reaction object of ID:".$id." exists.");
            }
            $this->id = $id;
        }
    }

    public static function get_instance(int $id) {
        return new Reaction($id, "", [], [], [], []);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_description() {
        global $DB;
        $sql = "select description from {stg_reaction} where ". $DB->sql_compare_text('id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_field_sql($sql, ['id' => $this->id]);
    }

    public function set_description(string $description) {
        global $DB;
        $DB->set_field('stg_reaction', 'description', $description, ['id' => $this->id]);
    }

    public function get_old_status() {
        global $DB;
        $sql = "select status from {stg_reaction_oldstatus} where "
        . $DB->sql_compare_text('reaction_id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_fieldset_sql($sql, ['id' => $this->id]);
    }

    public function set_old_status(array $status) {
        $oldstatus = Util::clean_array($status, 'string');
        global $DB;
        $DB->delete_records('stg_reaction_oldstatus', ['reaction_id' => $this->id]);
        foreach ($oldstatus as $status) {
            $DB->insert_record('stg_reaction_oldstatus', [
                'reaction_id' => $this->id,
                'status' => $status,
            ]);
        }
    }

    public function get_new_status() {
        global $DB;
        $sql = "select status from {stg_reaction_newstatus} where "
        . $DB->sql_compare_text('reaction_id') . " = ".$DB->sql_compare_text(':id');
        return $DB->get_fieldset_sql($sql, ['id' => $this->id]);
    }

    public function set_new_status(array $status) {
        $oldstatus = Util::clean_array($status, 'string');
        global $DB;
        $DB->delete_records('stg_reaction_newstatus', ['reaction_id' => $this->id]);
        foreach ($oldstatus as $status) {
            $DB->insert_record('stg_reaction_newstatus', [
                'reaction_id' => $this->id,
                'status' => $status,
            ]);
        }
    }

    public function get_old_item() {
        $items = [];
        global $DB;
        $sql = "select item_id from {stg_reaction_olditems} where "
        . $DB->sql_compare_text('reaction_id') . " = ".$DB->sql_compare_text(':id');
        $ids = $DB->get_fieldset_sql($sql, ['id' => $this->id]);
        foreach ($ids as $id) {
            array_push($items, Item::get_instance($id));
        }
        return $items;
    }

    public function set_old_item(array $item) {
        $items = Util::clean_array($item, Item_Interface::class);
        global $DB;
        $DB->delete_records('stg_reaction_olditems', ['reaction_id' => $this->id]);
        foreach ($items as $item) {
            $DB->insert_record('stg_reaction_olditems', [
                'reaction_id' => $this->id,
                'item_id' => $item->get_id(),
            ]);
        }
    }

    public function get_new_item() {
        $items = [];
        global $DB;
        $sql = "select item_id from {stg_reaction_newitems} where "
        . $DB->sql_compare_text('reaction_id') . " = ".$DB->sql_compare_text(':id');
        $ids = $DB->get_fieldset_sql($sql, ['id' => $this->id]);
        foreach ($ids as $id) {
            array_push($items, Item::get_instance($id));
        }
        return $items;
    }

    public function set_new_item(array $item) {
        $items = Util::clean_array($item, Item_Interface::class);
        global $DB;
        $DB->delete_records('stg_reaction_newitems', ['reaction_id' => $this->id]);
        foreach ($items as $item) {
            $DB->insert_record('stg_reaction_newitems', [
                'reaction_id' => $this->id,
                'item_id' => $item->get_id(),
            ]);
        }
    }

    public function do_reactions() {
        try {
            $characterreaction = Character_Reaction::get_instance_from_parent_id($this->id);
            return $characterreaction->do_reactions();
        } catch (Exception $e) {
            try {
                $locationreaction = Location_Reaction::get_instance_from_parent_id($this->id);
                return $locationreaction->do_reactions();
            } catch (Exception $e) {
                try {
                    $noentityreaction = No_Entity_Reaction::get_instance_from_parent_id($this->id);
                    return $noentityreaction->do_reactions();
                } catch (Exception $e) {
                    $e;
                }
            }
        }
        return [];
    }
}
