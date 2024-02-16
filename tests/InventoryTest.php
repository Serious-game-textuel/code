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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Id_Class.php');
use PHPUnit\Framework\TestCase;

class InventoryTest extends TestCase {
    /**
     * vérifie le bon fonctionnement du constructeur de la classe Inventory
     */
    public function testinventory() {
        $game = new Game(0, 0, [], new DateTime(),
        Language::FR, $this->createMock(Location_Interface::class), $this->createMock(Player_Character::class), null, null);
        $item = new Item("description1", "name1", ["status1"]);
        $item2 = new Item("description2", "name2", ["status2"]);
        $item3 = new Item("description3", "name3", ["status3"]);
        $playercharacter = new Player_Character("description", ["status"],
        $this->createMock(Inventory_Interface::class), $this->createMock(Location_Interface::class));

        $inventory = new Inventory([$item, $item2]);
        $this->assertEquals(true, $inventory->check_item($item2));
        $this->asserEquals(false, $inventory->check_item($item3));

        $this->assertEquals(null, $inventory->get_item(-1));
        $this->assertEquals($item2, $inventory->get_item($item2->get_id()));

        $this->assertEquals([$item, $item2], $inventory->get_items());
        $inventory->add_items([$item]);
        $this->assertEquals([$item, $item2], $inventory->get_items());
        $inventory->add_items([$item, $item2]);
        $this->assertEquals([$item, $item2], $inventory->get_items());
        $inventory->add_items([]);
        $this->assertEquals([$item, $item2], $inventory->get_items());
        $inventory->add_items([null]);
        $this->assertEquals([$item, $item2], $inventory->get_items());
        $inventory->add_items([null, $item3]);
        $this->assertEquals([$item, $item2, $item3], $inventory->get_items());
        $inventory->add_items([$playercharacter, 15]);
        $this->assertEquals([$item, $item2, $item3], $inventory->get_items());
        $inventory->remove_item([$item3]);
        $inventory->add_items([$item3, $playercharacter]);
        $this->assertEquals([$item, $item2], $inventory->get_items());
        $inventory->remove_item([null, $item, 15, $item2, $item3, null]);
        $this->assertEquals([], $inventory->get_items());
        $inventory->remove_item([$item, $item2]);
        $this->assertEquals([], $inventory->get_items());
        $inventory->add_items([$item, $item2]);
        $inventory->remove_item([]);
        $this->assertEquals([$item, $item2], $inventory->get_items());
        $inventory->add_items([$item, $item2]);
        $inventory->remove_item($item);
        $this->assertEquals([$item], $inventory->get_items());
        $this->assertEquals([$item, $item2], $inventory->get_items());
        $inventory->add_items([$item3]);
        $this->assertEquals([$item, $item2, $item3], $inventory->get_items());
    }
}
