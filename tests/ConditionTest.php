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
require_once($CFG->dirroot . '/mod/stg/src/classes/Node_Condition.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Leaf_Condition.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Condition.php');
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Entity_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Character_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Location_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Reaction.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Item.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Character.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Character_Reaction.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Location_Reaction.php');
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Inventory_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Game.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Player_Character.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Location.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/App.php');
require_once($CFG->dirroot . '/mod/stg/src/Language.php');

use PHPUnit\Framework\TestCase;

/**
 * Class ConditionTest
 * @package mod_stg
 * @copyright   2024 Paul Grandhomme, Loric Gallier, Benjamin Bracquier, Mathis Courant
 */
class ConditionTest extends TestCase {
    /**
     * vérifie le bon fonctionnement du constructeur de Node_Condition et des getters
     */
    public function testnodecondition() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);

        $condition1 = $this->createMock(Condition_Interface::class);
        $condition2 = $this->createMock(Condition_Interface::class);

        $nodecondition = new Node_Condition($condition1, $condition2, "et", []);

        $this->assertInstanceOf(Node_Condition::class, $nodecondition);
        $this->assertEquals($condition1, $nodecondition->get_condition1());
        $this->assertEquals($condition2, $nodecondition->get_condition2());
        $this->assertEquals("et", $nodecondition->get_connector());
    }
    /**
     * vérifie le bon fonctionnement du constructeur de Leaf_Condition et des getters
     */
    public function testleafcondition() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
        $entity1 = $this->createMock(Entity_Interface::class);
        $entity2 = $this->createMock(Entity_Interface::class);
        $condition = $this->createMock(Condition_Interface::class);

        $leafcondition = new Leaf_Condition($entity1, $entity2, "connector", ["status"], []);

        $this->assertInstanceOf(Leaf_Condition::class, $leafcondition);
        $this->assertEquals($entity1, $leafcondition->get_entity1());
        $this->assertEquals($entity2, $leafcondition->get_entity2());
        $this->assertEquals("connector", $leafcondition->get_connector());
        $this->assertEquals(["status"], $leafcondition->get_status());
    }
    /**
     * vérifie le bon fonctionnement de la méthode is_true pour une leaf_condition
     */
    public function testistrueforleafcondition() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        // Create mock objects for testing.
        $entity1 = new Character("description", "character", ["status"], [], $this->createMock(Location_Interface::class));
        $entity2 = new Character("description", "character2", ["status"], [], $this->createMock(Location_Interface::class));
        $condition = new Leaf_Condition($entity1, null, "est", ["status"], []);
        // Test when entity status matches condition status.
        $this->assertTrue($condition->is_true());
        $condition = new Leaf_Condition($entity1, null, "est", ["different_status"], []);
        // Test when entity status does not match condition status.
        $this->assertFalse($condition->is_true());
    }
    /**
     * vérifie le bon fonctionnement de la méthode is_true pour une leaf_condition un character qui possède ou pas un item
     */
    public function testistrueforleafconditionwithitem() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        // Create mock objects for testing.
        $entity1 = new Character("description", "character", ["status"], [], $this->createMock(Location_Interface::class));
        $entity2 = new Item("description", "item", ["status"]);
        // Test when entity1 has item.
        $entity1->get_inventory()->add_item($entity2);
        $condition = new Leaf_Condition($entity1, $entity2, "possède", [], []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition($entity1, $entity2, "a", [], []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition($entity1, $entity2, "possède pas", [], []);
        $this->assertFalse($condition->is_true());

        $condition = new Leaf_Condition($entity1, $entity2, "a pas", [], []);
        $this->assertFalse($condition->is_true());
        // Test when entity1 does not have item.
        $entity1->get_inventory()->remove_item($entity2);
        $condition = new Leaf_Condition($entity1, $entity2, "possède", [], []);
        $this->assertFalse($condition->is_true());

        $condition = new Leaf_Condition($entity1, $entity2, "a", [], []);
        $this->assertFalse($condition->is_true());

        // Test when entity1 does not have item.
        $condition = new Leaf_Condition($entity1, $entity2, "possède pas", [], []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition($entity1, $entity2, "a pas", [], []);
        $this->assertTrue($condition->is_true());

    }

    /**
     * vérifie le bon fonctionnement de la méthode is_true pour une leaf_condition un character qui est ou pas un status
     */
    public function testistrueforleafconditionwithstatus() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        // Create mock objects for testing.
        $entity1 = new Character("description", "character1", ["status"], [], $this->createMock(Location_Interface::class));
        $entity2 = new Character("description", "character2", ["status"], [], $this->createMock(Location_Interface::class));
        // Test when entity1 has status.
        $condition = new Leaf_Condition($entity1, null, "est", ["status"], []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition($entity1, null, "est", ["different_status"], []);
        $this->assertFalse($condition->is_true());
        // Test when entity1 does not have status.
        $condition = new Leaf_Condition($entity1, null, "est pas", ["different_status"], []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition($entity1, null, "est pas", ["status"], []);
        $this->assertFalse($condition->is_true());
    }

    /**
     * vérifie le bon fonctionnement de la méthode is_true pour une leaf_condition un item qui est ou pas un status
     */
    public function testistrueforleafconditionwithstatusitem() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        // Create mock objects for testing.
        $entity1 = new Item("description", "item", ["status"]);
        // Test when entity1 has status.
        $condition = new Leaf_Condition($entity1, null, "est", ["status"], []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition($entity1, null, "est", ["different_status"], []);
        $this->assertFalse($condition->is_true());
        // Test when entity1 does not have status.
        $condition = new Leaf_Condition($entity1, null, "est pas", ["different_status"], []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition($entity1, null, "est pas", ["status"], []);
        $this->assertFalse($condition->is_true());
    }

    /**
     * vérifie le bon fonctionnement de la méthode is_true pour une leaf_condition un location qui est ou pas un status
     */
    public function testistrueforleafconditionwithstatuslocation() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        // Create mock objects for testing.
        $entity1 = new Location("description", ["status"], [], [], [], 0);
        // Test when entity1 has status.
        $condition = new Leaf_Condition($entity1, null, "est", ["status"], []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition($entity1, null, "est", ["different_status"], []);
        $this->assertFalse($condition->is_true());
        // Test when entity1 does not have status.
        $condition = new Leaf_Condition($entity1, null, "est pas", ["different_status"], []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition($entity1, null, "est pas", ["status"], []);
        $this->assertFalse($condition->is_true());
    }

    /**
     * vérifie le bon fonctionnement de la méthode is_true pour une leaf_condition un location qui possède ou pas un item
     */
    public function testistrueforleafconditionwithitemlocation() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        // Create mock objects for testing.
        $entity1 = new Location("description", ["status"], [], [], [], 0);
        $entity2 = new Item("description", "item", ["status"]);
        // Test when entity1 has item.
        $entity1->get_inventory()->add_item($entity2);
        $condition = new Leaf_Condition($entity1, $entity2, "possède", [], []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition($entity1, $entity2, "a", [], []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition($entity1, $entity2, "possède pas", [], []);
        $this->assertFalse($condition->is_true());

        $condition = new Leaf_Condition($entity1, $entity2, "a pas", [], []);
        $this->assertFalse($condition->is_true());
        // Test when entity1 does not have item.
        $entity1->get_inventory()->remove_item($entity2);
        $condition = new Leaf_Condition($entity1, $entity2, "possède", [], []);
        $this->assertFalse($condition->is_true());

        $condition = new Leaf_Condition($entity1, $entity2, "a", [], []);
        $this->assertFalse($condition->is_true());

        // Test when entity1 does not have item.
        $condition = new Leaf_Condition($entity1, $entity2, "possède pas", [], []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition($entity1, $entity2, "a pas", [], []);
        $this->assertTrue($condition->is_true());

    }
    /**
     * vérifie le bon fonctionnement de la méthode is_true pour une node_condition
     */
    public function testistruefornodecondition() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        // Create mock objects for testing.
        $entity1 = new Character("description", "character", ["status"], [], $this->createMock(Location_Interface::class));
        $entity2 = new Character("description", "character2", ["status"], [], $this->createMock(Location_Interface::class));
        $condition1 = new Leaf_Condition($entity1, null, "est", ["status"], []);
        $condition2 = new Leaf_Condition($entity2, null, "est", ["status"], []);
        // Test with "et" connector.
        $nodecondition = new Node_Condition($condition1, $condition2, "&", []);
        $this->assertTrue($nodecondition->is_true());
        // Test with "ou" connector.
        $nodecondition = new Node_Condition($condition1, $condition2, "|", []);
        $this->assertTrue($nodecondition->is_true());
        // Test with "et" connector and one false condition.
        $condition2 = new Leaf_Condition($entity2, null, "est", ["different_status"], []);
        $nodecondition = new Node_Condition($condition1, $condition2, "&", []);
        $this->assertFalse($nodecondition->is_true());
        // Test with "ou" connector and one false condition.
        $nodecondition = new Node_Condition($condition1, $condition2, "|", []);
        $this->assertTrue($nodecondition->is_true());
    }
    /**
     * vérifie le bon fonctionnement de la méthode do_reactions pour une leaf_condition qui ajoute un status
     */
    public function testcharacterdoreactionsaddstatus() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        // Create mock objects for testing.
        $character = new Character("description", "character", ["status"], [], $this->createMock(Location_Interface::class));
        $newlocation = $this->createMock(Location_Interface::class);
        // Mock reactions.
        $characterreaction = new Character_Reaction('character_reaction', [], ['new_status'], [], [], $character, $newlocation);
        // Create a condition instance with reactions.
        $conditionwithreactions = new Leaf_Condition($character, null, 'est', ['status'], [$characterreaction]);
        // Test with true conditionstatus.
        $conditionwithreactions->do_reactions();
        $this->assertTrue(in_array('new_status', $character->get_status())); // Check if 'new_status' is in character status.

        // Test with false condition.
        $conditionwithreactions->do_reactions();
        $this->assertFalse(in_array('different_status', $character->get_status()));
        // Check if 'different_status' is not in character status.
    }

    /**
     * vérifie le bon fonctionnement de la méthode do_reactions pour une
     * leaf_condition pour un character_reaction qui retire un status
     */
    public function testcharacterdoreactionsremovestatus() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        // Create mock objects for testing.
        $character = new Character("description", "character", ["status"], [], $this->createMock(Location_Interface::class));
        $newlocation = $this->createMock(Location_Interface::class);
        // Mock reactions.
        $characterreaction = new Character_Reaction('character_reaction', ['status'], [], [], [], $character, $newlocation);
        // Create a condition instance with reactions.
        $conditionwithreactions = new Leaf_Condition($character, null, 'est pas', ['status'], [$characterreaction]);
        // Test with true conditionstatus.
        $conditionwithreactions->do_reactions();
        $this->assertFalse(in_array('status', $character->get_status())); // Check if 'status' is not in character status.

    }

    /**
     * vérifie le bon fonctionnement de la méthode do_reactions
     * pour une leaf_condition pour un character_reaction qui ajoute ou retire un item
     */
    public function testcharacterdoreactionsaddremoveitem() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        // Create mock objects for testing.
        $item1 = new Item("description", "item1", ["status"]);
        $character = new Character("description", "character", ["status"], [$item1], $this->createMock(Location_Interface::class));
        $item = new Item("description", "item", ["status"]);
        // Mock reactions.
        $characterreaction = new Character_Reaction('character_reaction', [], [], [], [$item], $character, null);
        // Create a condition instance with reactions.
        $conditionwithreactions = new Leaf_Condition($character, $item1, 'possède', [], [$characterreaction]);
        // Test with true conditionstatus.
        $conditionwithreactions->do_reactions();
        $this->assertTrue($character->has_item_character($item)); // Check if character has item.

        $characterreaction = new Character_Reaction('character_reaction', [], [], [$item], [], $character, null);
        $conditionwithreactions = new Leaf_Condition($character, $item1, 'possède', [], [$characterreaction]);
        // Test with false condition.
        $conditionwithreactions->do_reactions();
        $this->assertFalse($character->has_item_character($item)); // Check if character does not have item.
    }

    /**
     * vérifie le bon fonctionnement de la méthode do_reactions
     *  pour une leaf_condition pour un location_reaction qui ajoute ou retire un item
     */
    public function testlocationdoreactionsaddremoveitem() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        // Create mock objects for testing.
        $item1 = new Item("description", "item1", ["status"]);
        $location = new Location( "location", ["status"], [$item1], [], [], 0);
        $item = new Item("description", "item", ["status"]);
        // Mock reactions.
        $locationreaction = new Location_Reaction('location_reaction', [], [], [], [$item], $location);
        // Create a condition instance with reactions.
        $conditionwithreactions = new Leaf_Condition($location, $item1, 'possède', [], [$locationreaction]);
        // Test with true conditionstatus.
        $conditionwithreactions->do_reactions();
        $this->assertTrue($location->has_item_location($item)); // Check if location has item.

        $locationreaction = new Location_Reaction('location_reaction', [], [], [$item], [], $location);
        $conditionwithreactions = new Leaf_Condition($location, $item1, 'possède', [], [$locationreaction]);
        // Test with false condition.
        $conditionwithreactions->do_reactions();
        $this->assertFalse($location->has_item_location($item)); // Check if location does not have item.

    }

    /**
     * vérifie le bon fonctionnement de la méthode do_reactions
     *  pour une leaf_condition pour un location_reaction qui ajoute ou retire un status
     */
    public function testlocationdoreactionsaddremovestatus() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = new Game(0, 0, [], new DateTime(), $this->createMock(Player_Character::class), null, null, []);
        // Create mock objects for testing.
        $location = new Location( "location", ["status"], [], [], [], 0);
        // Mock reactions.
        $locationreaction = new Location_Reaction('location_reaction', [], ['new_status'], [], [], $location);

        // Create a condition instance with reactions.
        $conditionwithreactions = new Leaf_Condition($location, null, 'est', ['status'], [$locationreaction]);
        // Test with true conditionstatus.
        $conditionwithreactions->do_reactions();
        $this->assertTrue(in_array('new_status', $location->get_status())); // Check if location has status.

        $locationreaction = new Location_Reaction( 'location_reaction', ['new_status'], [], [], [], $location);
        $conditionwithreactions = new Leaf_Condition($location, null, 'est', ['status'], [$locationreaction]);
        // Test with false condition.
        $conditionwithreactions->do_reactions();
        $this->assertFalse(in_array('new_status', $location->get_status())); // Check if location does not have status.
    }
}
