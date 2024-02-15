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
        $game = new Game(0, 0, 0, [], new DateTime(),
         Language::FR, $this->createMock(Location_Interface::class), $this->createMock(Player_Character::class), null, null);
        // Create a mock reaction.
        // Create mock objects for testing.
        $item1 = new Item(1, "une pomme", "pomme", ["croquée"]);
        $item2 = new Item(2, "une poire", "poire", []);
        $item3 = new Item(3, "une banane", "banane", ["longue", "jaune"]);
        $item4 = new Item(4, "une fraise", "fraise", []);

        $character = new Character(1, "Un troll", "Michel", ["fatigué"], new Inventory(1, [[$item1]]));

        $location = new Location(1, "un marécage boueux",
         "marécages", ["boueux"], new Inventory(2, [[$item2, $item4]]), [$character], [], []);
        // Mock reactions.
        $characterreaction1 = new Character_Reaction(1, 'Michel récupère une poire', [], [], [], [$item2], $character, null);
        $characterreaction2 = new Character_Reaction(2, 'Michel grandit', [], ["grand"], [], [], $character, null);
        $characterreaction3 = new Character_Reaction(2, 'Michel perd sa fraise', [], [], [$item4], [], $character, null);

        $locationreaction1 = new Location_Reaction(1, 'une banane a apparu dans le marécage', [], [], [], [$item3], $location);
        $locationreaction2 = new Location_Reaction(2, 'une poire a disparu du marécage', [], [], [$item2], [], $location);

        // Create a condition instance with reactions.
        $conditionwithreactions1 = new Leaf_Condition(1, $character, $item1,
         'possède', [], null, [$characterreaction1, $characterreaction2, $characterreaction3]);
        $conditionwithreactions2 = new Leaf_Condition(2, $location, null, 'est', ["boueux"], null, []);
        $conditionwithreactions3 = new Leaf_Condition(3, $location, $item2, 'possède', [], null, []);

        $conditionwithreactions4 = new Node_Condition(1, $conditionwithreactions2, $conditionwithreactions3,
            "et", [$locationreaction1, $locationreaction2]);
        $action = new Action(1, 'action', [$conditionwithreactions1, $conditionwithreactions4]);

        // Test the do_condition method.
        $result = $action->do_conditions();
        // Check le personnage a gardé son item.
        $this->assertTrue($character->has_item_character($item1));
        // Check le personnage a gagné un item.
        $this->assertTrue($character->has_item_character($item2));
        // Check le personnage a pas gagné un item indésirable.
        $this->assertFalse($character->has_item_character($item3));
        // Check le personnage a perdu un item.
        $this->assertFalse($character->has_item_character($item4));
        // Check if character has item1 donc que la réaction a bien été effectuée.
        $this->assertTrue(in_array('grand', $character->get_status()));
        // Check if character has new_status donc que la réaction a bien été effectuée.
        $this->assertFalse($location->has_item_location($item1));
        // Check if location has item1 donc que la réaction a bien été effectuée.
        $this->assertTrue($location->has_item_location($item2));
        // Check if character has new_status donc que la réaction a bien été effectuée.
        $this->assertTrue($location->has_item_location($item3));
        // Check if location has item1 donc que la réaction a bien été effectuée.
        $this->assertTrue($location->has_item_location($item4));
        // Check if location has item donc que la condition est vraie.
    }

}

