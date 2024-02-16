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

class EntityTest extends TestCase {
     /**
      * vérifie le bon fonctionnement du constructeur de la classe Entity
      */
    public function testnpc() {
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        $npccharacter = new Npc_Character("description", "name", ["status"], [], $this->createMock(Location_Interface::class));

        $this->assertEquals("description", $npccharacter->get_description());
        $this->assertEquals("name", $npccharacter->get_name());
        $this->assertEquals(["status"], $npccharacter->get_status());
    }
    public function testplayer() {
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        $playercharacter = new Player_Character(
            "description",
            "name",
            ["status"],
            [],
            $this->createMock(Location_Interface::class));

        $this->assertEquals("description", $playercharacter->get_description());
        $this->assertEquals(["status"], $playercharacter->get_status());
    }
    public function testitem() {
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        $item = new Item("description", "name", ["status"]);
        $this->assertEquals("description", $item->get_description());
        $this->assertEquals("name", $item->get_name());
        $this->assertEquals(["status"], $item->get_status());
    }
    public function testlocation() {
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        $location = new Location("name", ["status"], [], [], [], []);
        $this->assertEquals("description", $location->get_description());
        $this->assertEquals("name", $location->get_name());
        $this->assertEquals(["status"], $location->get_status());
    }

    /**
     * vérifie le bon fonctionnement des changements de status
     */
    public function teststatus() {
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        $npccharacter = new Npc_Character("description", "name", ["status"], [], $this->createMock(Location_Interface::class));
        $playercharacter = new Player_Character(
            "description",
            "name", ["status"],
            [],
            $this->createMock(Location_Interface::class));
        $item = new Item("description", "nameitem", ["status"]);
        $location = new Location("namelocation", ["status"], [], [], [], []);

            $npccharacter->add_status(["new_status"]);
            $this->assertEquals(["status", "new_status"], $npccharacter->get_status());
            $npccharacter->remove_status(["status"]);
            $this->assertEquals(["new_status"], $npccharacter->get_status());
            $npccharacter->remove_status(["status"]);
            $this->assertEquals(["new_status"], $npccharacter->get_status());
            $npccharacter->add_status(["new_status"]);
            $this->assertEquals(["new_status"], $npccharacter->get_status());

            $playercharacter->add_status(["new_status"]);
            $this->assertEquals(["status", "new_status"], $playercharacter->get_status());
            $playercharacter->remove_status(["status"]);
            $this->assertEquals(["new_status"], $playercharacter->get_status());
            $playercharacter->remove_status(["status"]);
            $this->assertEquals(["new_status"], $playercharacter->get_status());
            $playercharacter->add_status(["new_status"]);
            $this->assertEquals(["new_status"], $playercharacter->get_status());

            $item->add_status(["new_status"]);
            $this->assertEquals(["status", "new_status"], $item->get_status());
            $item->remove_status(["status"]);
            $this->assertEquals(["new_status"], $item->get_status());
            $item->remove_status(["status"]);
            $this->assertEquals(["new_status"], $item->get_status());
            $item->add_status(["new_status"]);
            $this->assertEquals(["new_status"], $item->get_status());

            $location->add_status(["new_status"]);
            $this->assertEquals(["status", "new_status"], $location->get_status());
            $location->remove_status(["status"]);
            $this->assertEquals(["new_status"], $location->get_status());
            $location->remove_status(["status"]);
            $this->assertEquals(["new_status"], $location->get_status());
            $location->add_status(["new_status"]);
            $this->assertEquals(["new_status"], $location->get_status());
    }
}
