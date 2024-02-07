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

// vendor/bin/phpunit src/tests/ConditionTest.php
require_once 'serioustextualgame/src/interfaces/Condition_Interface.php';
require_once 'serioustextualgame/src/classes/Node_Condition.php';
require_once 'serioustextualgame/src/classes/Leaf_Condition.php';
require_once 'serioustextualgame/src/classes/Condition.php';
require_once 'serioustextualgame/src/interfaces/Entity_Interface.php';
require_once 'serioustextualgame/src/interfaces/Character_Interface.php';
require_once 'serioustextualgame/src/interfaces/Location_Interface.php';
require_once 'serioustextualgame/src/classes/Reaction.php';
require_once 'serioustextualgame/src/classes/Item.php';
require_once 'serioustextualgame/src/classes/Character.php';
require_once 'serioustextualgame/src/classes/Character_Reaction.php';
require_once 'serioustextualgame/src/classes/Location_Reaction.php';
require_once 'serioustextualgame/src/interfaces/Inventory_Interface.php';

use PHPUnit\Framework\TestCase;
use SebastianBergmann\Environment\Console;

class ConditionTest extends TestCase {
    /**
     * vérifie le bon fonctionnement du constructeur de Node_Condition et des getters
     */
    public function testNodeCondition() {
        $condition1 = $this->createMock(Condition_Interface::class);
        $condition2 = $this->createMock(Condition_Interface::class);

        $nodeCondition = new Node_Condition(1, $condition1, $condition2, "et", []);

        $this->assertInstanceOf(Node_Condition::class, $nodeCondition);
        $this->assertEquals($condition1, $nodeCondition->get_condition1());
        $this->assertEquals($condition2, $nodeCondition->get_condition2());
        $this->assertEquals("et", $nodeCondition->get_connector1());
    }
    /**
     * vérifie le bon fonctionnement du constructeur de Leaf_Condition et des getters
     */
    public function testLeafCondition() {
        $entity1 = $this->createMock(Entity_Interface::class);
        $entity2 = $this->createMock(Entity_Interface::class);
        $condition = $this->createMock(Condition_Interface::class);

        $leafCondition = new Leaf_Condition(2, $entity1, $entity2, "connector", ["status"], $condition, []);

        $this->assertInstanceOf(Leaf_Condition::class, $leafCondition);
        $this->assertEquals($entity1, $leafCondition->get_entity1());
        $this->assertEquals($entity2, $leafCondition->get_entity2());
        $this->assertEquals("connector", $leafCondition->get_connector());
        $this->assertEquals(["status"], $leafCondition->get_status());
        $this->assertEquals($condition, $leafCondition->get_condition());
    }
    /**
     * vérifie le bon fonctionnement de la méthode is_true pour une leaf_condition
     */
    public function testIsTrueForLeafCondition() {
        // Create mock objects for testing
        $entity1 = new Character(1, "character", "description",["status"],new Inventory(3,[]));
        $entity2 = new Character(4, "character", "description",["status"],new Inventory(5,[]));
        $condition = new Leaf_Condition(2, $entity1, null, "est", ["status"], null, []);
    
        // Test when entity status matches condition status
       /* echo "Entity1 status: ";
        print_r($entity1->get_status());
        echo "\nCondition status: ";
        print_r($condition->get_status());
        echo "\n";*/
        $this->assertTrue($condition->is_true());
        
        $condition = new Leaf_Condition(3, $entity1, null, "est", ["different_status"], null, []);
      /*  echo "\nCondition status: ";
        print_r($condition->get_status());
        echo "\n";*/
        // Test when entity status does not match condition status
        $this->assertFalse($condition->is_true());
    }
    /**
     * vérifie le bon fonctionnement de la méthode is_true pour une leaf_condition un character qui possède ou pas un item
     */
    public function testIsTrueForLeafConditionWithItem() {
        // Create mock objects for testing
        $entity1 = new Character(1, "character", "description",["status"],new Inventory(3,[]));
        $entity2 = new Item(4, "item", "description",["status"]);
        // Test when entity1 has item
        $entity1->get_inventory()->add_item([$entity2]);
        $condition = new Leaf_Condition(2, $entity1, $entity2, "possède", [], null, []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition(2, $entity1, $entity2, "a", [], null, []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition(3, $entity1, $entity2, "possède pas", [], null, []);
        $this->assertFalse($condition->is_true());

        $condition = new Leaf_Condition(3, $entity1, $entity2, "a pas", [], null, []);
        $this->assertFalse($condition->is_true());

    
        // Test when entity1 does not have item
        $entity1->get_inventory()->remove_item([$entity2]);
        $condition = new Leaf_Condition(3, $entity1, $entity2, "possède", [], null, []);
        $this->assertFalse($condition->is_true());

        $condition = new Leaf_Condition(3, $entity1, $entity2, "a", [], null, []);
        $this->assertFalse($condition->is_true());

        // Test when entity1 does not have item
        $condition = new Leaf_Condition(3, $entity1, $entity2, "possède pas", [], null, []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition(3, $entity1, $entity2, "a pas", [], null, []);
        $this->assertTrue($condition->is_true());

    }

    /**
     * vérifie le bon fonctionnement de la méthode is_true pour une leaf_condition un character qui est ou pas un status
     */
    public function testIsTrueForLeafConditionWithStatus() {
        // Create mock objects for testing
        $entity1 = new Character(1, "character", "description",["status"],new Inventory(3,[]));
        $entity2 = new Character(4, "character", "description",["status"],new Inventory(5,[]));
        
        // Test when entity1 has status
        $condition = new Leaf_Condition(2, $entity1, null, "est", ["status"], null, []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition(3, $entity1, null, "est", ["different_status"], null, []);
        $this->assertFalse($condition->is_true());

    
        // Test when entity1 does not have status
        $condition = new Leaf_Condition(3, $entity1, null, "est pas", ["different_status"], null, []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition(3, $entity1, null, "est pas", ["status"], null, []);
        $this->assertFalse($condition->is_true());
    }

    /**
     * vérifie le bon fonctionnement de la méthode is_true pour une leaf_condition un item qui est ou pas un status
     */
    public function testIsTrueForLeafConditionWithStatusItem() {
        // Create mock objects for testing
        $entity1 = new Item(1, "item", "description",["status"]);
        
        // Test when entity1 has status
        $condition = new Leaf_Condition(2, $entity1, null, "est", ["status"], null, []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition(3, $entity1, null, "est", ["different_status"], null, []);
        $this->assertFalse($condition->is_true());

    
        // Test when entity1 does not have status
        $condition = new Leaf_Condition(3, $entity1, null, "est pas", ["different_status"], null, []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition(3, $entity1, null, "est pas", ["status"], null, []);
        $this->assertFalse($condition->is_true());
    }

    /**
     * vérifie le bon fonctionnement de la méthode is_true pour une leaf_condition un location qui est ou pas un status
     */
    public function testIsTrueForLeafConditionWithStatusLocation() {
        // Create mock objects for testing
        $entity1 = new Location(1, "location", "description",["status"],new Inventory(3,[]),[],[],[]);
        
        // Test when entity1 has status
        $condition = new Leaf_Condition(2, $entity1, null, "est", ["status"], null, []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition(3, $entity1, null, "est", ["different_status"], null, []);
        $this->assertFalse($condition->is_true());

    
        // Test when entity1 does not have status
        $condition = new Leaf_Condition(3, $entity1, null, "est pas", ["different_status"], null, []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition(3, $entity1, null, "est pas", ["status"], null, []);
        $this->assertFalse($condition->is_true());
    }

    /**
     * vérifie le bon fonctionnement de la méthode is_true pour une leaf_condition un location qui possède ou pas un item
     */
    public function testIsTrueForLeafConditionWithItemLocation() {
        // Create mock objects for testing
        $entity1 = new Location(1, "location", "description",["status"],new Inventory(3,[]),[],[],[]);
        $entity2 = new Item(4, "item", "description",["status"]);
        // Test when entity1 has item
        $entity1->get_inventory()->add_item([$entity2]);
        $condition = new Leaf_Condition(2, $entity1, $entity2, "possède", [], null, []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition(2, $entity1, $entity2, "a", [], null, []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition(3, $entity1, $entity2, "possède pas", [], null, []);
        $this->assertFalse($condition->is_true());

        $condition = new Leaf_Condition(3, $entity1, $entity2, "a pas", [], null, []);
        $this->assertFalse($condition->is_true());

    
        // Test when entity1 does not have item
        $entity1->get_inventory()->remove_item([$entity2]);
        $condition = new Leaf_Condition(3, $entity1, $entity2, "possède", [], null, []);
        $this->assertFalse($condition->is_true());

        $condition = new Leaf_Condition(3, $entity1, $entity2, "a", [], null, []);
        $this->assertFalse($condition->is_true());

        // Test when entity1 does not have item
        $condition = new Leaf_Condition(3, $entity1, $entity2, "possède pas", [], null, []);
        $this->assertTrue($condition->is_true());

        $condition = new Leaf_Condition(3, $entity1, $entity2, "a pas", [], null, []);
        $this->assertTrue($condition->is_true());

    }
    /**
     * vérifie le bon fonctionnement de la méthode is_true pour une node_condition
     */
    public function testIsTrueForNodeCondition() {
        // Create mock objects for testing
        $entity1 = new Character(1, "character", "description",["status"],new Inventory(3,[]));
        $entity2 = new Character(4, "character", "description",["status"],new Inventory(5,[]));
        $condition1 = new Leaf_Condition(2, $entity1, null, "est", ["status"], null, []);
        $condition2 = new Leaf_Condition(3, $entity2, null, "est", ["status"], null, []);
    
        // Test with "et" connector
        $nodeCondition = new Node_Condition(4, $condition1, $condition2, "et", []);
        $this->assertTrue($nodeCondition->is_true());
    
        // Test with "ou" connector
        $nodeCondition = new Node_Condition(5, $condition1, $condition2, "ou", []);
        $this->assertTrue($nodeCondition->is_true());
    
        // Test with "et" connector and one false condition
        $condition2 = new Leaf_Condition(6, $entity2, null, "est", ["different_status"], null, []);
        $nodeCondition = new Node_Condition(7, $condition1, $condition2, "et", []);
        $this->assertFalse($nodeCondition->is_true());
    
        // Test with "ou" connector and one false condition
        $nodeCondition = new Node_Condition(8, $condition1, $condition2, "ou", []);
        $this->assertTrue($nodeCondition->is_true());
    }
    /**
     * vérifie le bon fonctionnement de la méthode do_reactions pour une leaf_condition qui ajoute un status
     */
    public function testCharacterDoReactionsAddStatus() {
        // Create mock objects for testing
        $character = new Character(1, "character", "description",["status"],new Inventory(3,[]));
        $newLocation = $this->createMock(Location_Interface::class);
    
        // Mock reactions
        $characterReaction = new Character_Reaction(1, 'character_reaction', [], ['new_status'], [], [], $character, $newLocation);
    

        // Create a condition instance with reactions
        $conditionWithReactions = new Leaf_Condition(4, $character, null, 'est', ['status'], null, [$characterReaction]);
    
        // Test with true conditionstatus
        $conditionWithReactions->do_reactions();
        $this->assertTrue(in_array('new_status', $character->get_status())); // Check if 'new_status' is in character status

        // Test with false condition
        $conditionWithReactions->do_reactions();
        $this->assertFalse(in_array('different_status', $character->get_status())); // Check if 'different_status' is not in character status
    }

    /**
     * vérifie le bon fonctionnement de la méthode do_reactions pour une leaf_condition pour un character_reaction qui retire un status
     */
    public function testCharacterDoReactionsRemoveStatus() {
        // Create mock objects for testing
        $character = new Character(1, "character", "description",["status"],new Inventory(3,[]));
        $newLocation = $this->createMock(Location_Interface::class);
    
        // Mock reactions
        $characterReaction = new Character_Reaction(1, 'character_reaction', ['status'], [], [], [], $character, $newLocation);
    

        // Create a condition instance with reactions
        $conditionWithReactions = new Leaf_Condition(4, $character, null, 'est pas', ['status'], null, [$characterReaction]);
    
        // Test with true conditionstatus
        $conditionWithReactions->do_reactions();
        $this->assertFalse(in_array('status', $character->get_status())); // Check if 'status' is not in character status

    }

    /**
     * vérifie le bon fonctionnement de la méthode do_reactions pour une leaf_condition pour un character_reaction qui ajoute ou retire un item
     */
    public function testCharacterDoReactionsAddRemoveItem() {
        // Create mock objects for testing
        $item1 = new Item(5, "item1", "description",["status"]);
        $character = new Character(1, "character", "description",["status"],new Inventory(3,[[$item1]]));
        $item = new Item(4, "item", "description",["status"]);
        // Mock reactions
        $characterReaction = new Character_Reaction(1, 'character_reaction', [], [], [], [$item], $character, null);
    

        // Create a condition instance with reactions
        $conditionWithReactions = new Leaf_Condition(4, $character, $item1, 'possède', [], null, [$characterReaction]);
    
        // Test with true conditionstatus
        $conditionWithReactions->do_reactions();
        $this->assertTrue($character->has_item_character($item)); // Check if character has item

        $characterReaction = new Character_Reaction(1, 'character_reaction', [], [], [$item], [], $character, null);
        $conditionWithReactions = new Leaf_Condition(4, $character, $item1, 'possède', [], null, [$characterReaction]);
        // Test with false condition
        $conditionWithReactions->do_reactions();
        $this->assertFalse($character->has_item_character($item)); // Check if character does not have item
    }

    /**
     * vérifie le bon fonctionnement de la méthode do_reactions pour une leaf_condition pour un location_reaction qui ajoute ou retire un item
     */
    public function testLocationDoReactionsAddRemoveItem() {
        // Create mock objects for testing
        $item1 = new Item(5, "item1", "description",["status"]);
        $location = new Location(1, "location", "description",["status"],new Inventory(3,[[$item1]]),[],[],[]);
        $item = new Item(4, "item", "description",["status"]);
        // Mock reactions
        $locationReaction = new Location_Reaction(1, 'location_reaction', [], [], [], [$item], $location);
    

        // Create a condition instance with reactions
        $conditionWithReactions = new Leaf_Condition(4, $location, $item1, 'possède', [], null, [$locationReaction]);
    
        // Test with true conditionstatus
        $conditionWithReactions->do_reactions();
        $this->assertTrue($location->has_item_location($item)); // Check if location has item

        $locationReaction = new Location_Reaction(1, 'location_reaction', [], [], [$item], [], $location);
        $conditionWithReactions = new Leaf_Condition(4, $location, $item1, 'possède', [], null, [$locationReaction]);
        // Test with false condition
        $conditionWithReactions->do_reactions();
        $this->assertFalse($location->has_item_location($item)); // Check if location does not have item

    }

    /**
     * vérifie le bon fonctionnement de la méthode do_reactions pour une leaf_condition pour un location_reaction qui ajoute ou retire un status
     */
    public function testLocationDoReactionsAddRemoveStatus() {
        // Create mock objects for testing
        $location = new Location(1, "location", "description",["status"],new Inventory(3,[]),[],[],[]);
        // Mock reactions
        $locationReaction = new Location_Reaction(1, 'location_reaction', [], ['new_status'], [], [], $location);
    

        // Create a condition instance with reactions
        $conditionWithReactions = new Leaf_Condition(4, $location, null, 'est', ['status'], null, [$locationReaction]);
    
        // Test with true conditionstatus
        $conditionWithReactions->do_reactions();
        $this->assertTrue(in_array('new_status', $location->get_status())); // Check if location has status

        $locationReaction = new Location_Reaction(1, 'location_reaction', ['new_status'], [], [], [], $location);
        $conditionWithReactions = new Leaf_Condition(4, $location, null, 'est', ['status'], null, [$locationReaction]);
        // Test with false condition
        $conditionWithReactions->do_reactions();
        $this->assertFalse(in_array('new_status', $location->get_status())); // Check if location does not have status

    }
}
?>
