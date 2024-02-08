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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Node_Condition.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Leaf_Condition.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Condition.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Entity_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Character_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Location_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Reaction.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Item.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Character.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Character_Reaction.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Location_Reaction.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Inventory_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Action.php');
use PHPUnit\Framework\TestCase;

class ActionTest extends TestCase {
    /**
     * vérifie que quand on appelle la méthode do_conditions, les conditions sont bien effectuées
     */
    public function testdoconditions() {
        // Create a mock reaction.
        // Create mock objects for testing.
        $item1 = new Item(5, "1 item", "item1", ["status"]);
        $character = new Character(1, "creation character", "Michel", ["status"], new Inventory(3, [[$item1]]));
        $location = new Location(1, "creation location",
         "location", ["status"], new Inventory(3, [[$item1]]), [$character], [], []);
        $item = new Item(4, "une description de item", "item", ["status"]);
        $item2 = new Item(6, "une description de item2", "item2", ["status"]);
        // Mock reactions.
        $characterreaction = new Character_Reaction(1, 'je ajoute un item', [], [], [], [$item], $character, null);

        $characterreaction1 = new Character_Reaction(2, 'je change un status', [], ["new_status"], [], [], $character, null);
        $locationreaction = new Location_Reaction(1, 'jajoute un item a une location', [], [], [], [$item], $location);

        $locationreaction1 = new Location_Reaction(2, 'jajoute un item a une location', [], [], [], [$item2], $location);

        // Create a condition instance with reactions.
        $conditionwithreactions = new Leaf_Condition(4, $character, $item1,
         'possède', [], null, [$characterreaction, $characterreaction1]);

        $conditionwithreactions1 = new Leaf_Condition(5, $location, $item1, 'possède', [], null, [$locationreaction]);
        $conditionwithreactions2 = new Leaf_Condition(6, $location, $item2, 'possède', [], null, [$locationreaction1]);
        $action = new Action(1, $character, $item,
         'donner', [$conditionwithreactions, $conditionwithreactions1, $conditionwithreactions2]);

        // Test the do_condition method.
        $action->do_conditions();
        $this->assertTrue($character->has_item_character($item));
        // Check if character has item donc que la condition est vraie.
        $this->assertTrue($character->has_item_character($item1));
        // Check if character has item1 donc que la réaction a bien été effectuée.
        $this->assertTrue(in_array('new_status', $character->get_status()));
        // Check if character has new_status donc que la réaction a bien été effectuée.
        $this->assertTrue($location->has_item_location($item1));
        // Check if location has item1 donc que la réaction a bien été effectuée.
        $this->assertTrue($location->has_item_location($item));
        // Check if location has item donc que la condition est vraie.
        $this->assertFalse($location->has_item_location($item2));
        // Check if location has item2 donc que la condition est fausse et que la réaction n'a pas été effectuée.
    }

}

