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
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Condition_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Entity_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Character_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Location_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Item.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Character.php');
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Inventory_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Action.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Inventory.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Location.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Entity.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Npc_Character.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Player_Character.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Game.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/App.php');
require_once($CFG->dirroot . '/mod/stg/src/Language.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Node_Condition.php');
use PHPUnit\Framework\TestCase;

/**
 * Class EntityTest
<<<<<<< HEAD
 * @package mod_stg
=======
 * @package mod_serioustextualgame
>>>>>>> exceptions
 */
class EntityTest extends TestCase {


    public function testnpc() {
        global $CFG;
<<<<<<< HEAD
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
=======
        $app = new App(file_get_contents($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv'), Language::FR);
>>>>>>> exceptions
        $npccharacter = new Npc_Character("description", "name", ["status"], [], $this->createMock(Location_Interface::class));

        $this->assertInstanceOf(Npc_Character::class, $npccharacter);
        $this->assertEquals("description", $npccharacter->get_description());
        $this->assertEquals("name", $npccharacter->get_name());
        $this->assertEquals(["status"], $npccharacter->get_status());
    }
    public function testplayer() {
        global $CFG;
<<<<<<< HEAD
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
=======
        $app = new App(file_get_contents($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv'), Language::FR);
>>>>>>> exceptions
        $playercharacter = new Player_Character(
            "description",
            "name",
            ["status"],
            [],
            $this->createMock(Location_Interface::class));

        $this->assertInstanceOf(Player_Character::class, $playercharacter);
        $this->assertEquals("description", $playercharacter->get_description());
        $this->assertEquals(["status"], $playercharacter->get_status());
    }
    public function testitem() {
        global $CFG;
<<<<<<< HEAD
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
=======
        $app = new App(file_get_contents($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv'), Language::FR);
>>>>>>> exceptions
        $item = new Item("description", "name", ["status"]);
        $this->assertEquals("description", $item->get_description());
        $this->assertEquals("name", $item->get_name());
        $this->assertEquals(["status"], $item->get_status());
    }
    public function testlocation() {
        global $CFG;
<<<<<<< HEAD
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
=======
        $app = new App(file_get_contents($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv'), Language::FR);
>>>>>>> exceptions
        $location = new Location("name", ["status"], [], [], [], 0);
        $this->assertEquals("name", $location->get_name());
        $this->assertEquals(["status"], $location->get_status());
    }

    /**
     * vérifie le bon fonctionnement des changements de status
     */
    public function teststatus() {
        global $CFG;
<<<<<<< HEAD
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
=======
        $app = new App(file_get_contents($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv'), Language::FR);
>>>>>>> exceptions
        $npccharacter = new Npc_Character("description", "namenpc", ["status"], [], $this->createMock(Location_Interface::class));
        $playercharacter = new Player_Character(
            "description",
            "nameplayer", ["status"],
            [],
            $this->createMock(Location_Interface::class));
        $item = new Item("description", "nameitem", ["status"]);
        $location = new Location("namelocation", ["status"], [], [], [], 0);

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
