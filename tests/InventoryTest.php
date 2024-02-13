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
    public function testinventory(){
            $game = new Game(0, 0, [], new DateTime(),
            Language::FR, $this->createMock(Location_Interface::class), $this->createMock(Player_Character::class), null, null);
            $Item = new Item("description1", "name1", ["status1"]);
            $Item2 = new Item("description2", "name2", ["status2"]);
            $Item3 = new Item("description3", "name3", ["status3"]);
            $Player_Character = new Player_Character("description", ["status"], $this->createMock(Inventory_Interface::class), $this->createMock(Location_Interface::class));

            $Inventory = new Inventory([$Item, $Item2]);
            $this->assertEquals(true,$Inventory->check_item($Item2));
            $this->asserEquals(false, $Inventory->check_item($Item3));

            $this->assertEquals(null, $Inventory->get_item(-1));
            $this->assertEquals($Item2, $Inventory->get_item($Item2->get_id()));

            $this->assertEquals([$Item, $Item2],$Inventory->get_items());
            $Inventory->add_items([$Item]);
            $this->assertEquals([$Item, $Item2],$Inventory->get_items());
            $Inventory->add_items([$Item, $Item2]);
            $this->assertEquals([$Item, $Item2],$Inventory->get_items());
            $Inventory->add_items([]);
            $this->assertEquals([$Item, $Item2],$Inventory->get_items());
            $Inventory->add_items([null]);
            $this->assertEquals([$Item, $Item2],$Inventory->get_items());
            $Inventory->add_items([null,$Item3]);
            $this->assertEquals([$Item, $Item2, $Item3],$Inventory->get_items());
            $Inventory->add_items([$Player_Character,15]);
            $this->assertEquals([$Item, $Item2, $Item3],$Inventory->get_items());
            $Inventory->remove_item([$Item3,]);
            $Inventory->add_items([$Item3,$Player_Character]);
            $this->assertEquals([$Item, $Item2],$Inventory->get_items());
            $Inventory->remove_item([null,$Item,15,$Item2,$Item3,null]);
            $this->assertEquals([],$Inventory->get_items());
            $Inventory->remove_item([$Item, $Item2,]);
            $this->assertEquals([],$Inventory->get_items());
            $Inventory->add_items([$Item, $Item2]);
            $Inventory->remove_item([]);
            $this->assertEquals([$Item, $Item2],$Inventory->get_items());
            $Inventory->add_items([$Item, $Item2]);
            $Inventory->remove_item($Item);
            $this->assertEquals([$Item],$Inventory->get_items());






            $this->assertEquals([$Item, $Item2],$Inventory->get_items());
            $Inventory->add_items([$Item3]);
            $this->assertEquals([$Item, $Item2,$Item3],$Inventory->get_items());



    }
    
}