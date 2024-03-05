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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Entity_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Character_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Location_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Item.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Character.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Inventory_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Action.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Inventory.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Location.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Entity.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Npc_Character.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Player_Character.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Game.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/App.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/Language.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Item_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Node_Condition.php');
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\isInstanceOf;

class InventoryTest extends TestCase {
    /**
     * vérifie le bon fonctionnement du constructeur de la classe Inventory
     */
    public function testinventory() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = $app->get_game();
        $item = new Item("description1", "name1", ["status1"]);
        $item2 = new Item("description2", "name2", ["status2"]);
        $inventory = new Inventory([$item, $item2]);
        $this->assertInstanceOf(Inventory::class, $inventory);
    }
    public function testcheckitem() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = $app->get_game();
        $item = new Item("description1", "name1", ["status1"]);
        $item2 = new Item("description2", "name2", ["status2"]);
        $item3 = new Item("description3", "name3", ["status3"]);

        $inventory = new Inventory([$item, $item2]);
        $this->assertTrue($inventory->check_item($item2));
        $this->assertFalse($inventory->check_item($item3));
    }

    public function testgetitem() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = $app->get_game();
        $item = new Item("description1", "name1", ["status1"]);
        $item2 = new Item("description2", "name2", ["status2"]);
        $item3 = new Item("description3", "name3", ["status3"]);

        $inventory = new Inventory([$item, $item2]);
        $this->assertEquals(null, $inventory->get_item(-1));
        $this->assertEquals($item2, $inventory->get_item($item2->get_id()));
        $this->assertEquals($item, $inventory->get_item($item->get_id()));
        $this->assertEquals(null, $inventory->get_item($item3->get_id()));
    }

    public function testaddremove() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = $app->get_game();
        $item = new Item("description1", "name1", ["status1"]);
        $item2 = new Item("description2", "name2", ["status2"]);
        $item3 = new Item("description3", "name3", ["status3"]);

        $inventory = new Inventory([$item, $item2]);
        $inventory->add_item($item);
        $this->assertEquals([$item2, $item], $inventory->get_items());
        $inventory->add_item($item);
        $inventory->add_item($item2);
        $this->assertEquals([$item, $item2], $inventory->get_items());
        $inventory->add_item($item3);
        $this->assertEquals([$item, $item2, $item3], $inventory->get_items());
        $inventory->remove_item($item3);
        $this->assertEquals([$item, $item2], $inventory->get_items());
        $inventory->remove_item($item3);
        $this->assertEquals([$item, $item2], $inventory->get_items());
    }
}
