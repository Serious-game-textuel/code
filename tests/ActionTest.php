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
require_once($CFG->dirroot . '/mod/stg/src/classes/Action.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/App.php');
require_once($CFG->dirroot . '/mod/stg/src/Language.php');
use PHPUnit\Framework\TestCase;

/**
 * Class ActionTest
<<<<<<< HEAD
 * @package mod_stg
=======
 * @package mod_serioustextualgame
>>>>>>> exceptions
 */
class ActionTest extends TestCase {
    /**
     * vérifie que quand on appelle la méthode do_conditions, les conditions sont bien effectuées
     */
    public function testdoconditions() {
        global $CFG;
<<<<<<< HEAD
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
=======
        $app = new App(file_get_contents($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv'), Language::FR);
>>>>>>> exceptions
        // Create a mock reaction.
        // Create mock objects for testing.
        $item1 = new Item("une pomme", "pomme", ["croquée"]);
        $item2 = new Item("une poire", "poire", []);
        $item3 = new Item("une banane", "banane", ["longue", "jaune"]);
        $item4 = new Item("une fraise", "fraise", []);

        $location = new Location("un marécage boueux", ["boueux"], [$item2, $item4], [], [], 0 );
        $character = new Character("Un troll", "Michel", ["fatigué"], [$item1], $location);

        // Mock reactions.
        $characterreaction1 = new Character_Reaction('Michel récupère une poire', [], [], [], [$item2], $character, null);
        $characterreaction2 = new Character_Reaction('Michel grandit', [], ["grand"], [], [], $character, null);
        $characterreaction3 = new Character_Reaction('Michel perd sa fraise', [], [], [$item4], [], $character, null);

        $locationreaction1 = new Location_Reaction('une banane a apparu dans le marécage', [], [], [], [$item3], $location);
        $locationreaction2 = new Location_Reaction('une poire a disparu du marécage', [], [], [$item2], [], $location);

        // Create a condition instance with reactions.
        $conditionwithreactions1 = new Leaf_Condition($character, $item1,
         'a', [], [$characterreaction1, $characterreaction2, $characterreaction3]);
        $conditionwithreactions2 = new Leaf_Condition($location, null, 'est', ["boueux"], [], []);
        $conditionwithreactions3 = new Leaf_Condition($location, $item2, 'a', [], [], []);

        $conditionwithreactions4 = new Node_Condition($conditionwithreactions2, $conditionwithreactions3,
            "&", [$locationreaction1, $locationreaction2]);
        $action = new Action('action', [$conditionwithreactions1, $conditionwithreactions4]);

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
        $this->assertFalse($location->has_item_location($item2));
        // Check if character has new_status donc que la réaction a bien été effectuée.
        $this->assertTrue($location->has_item_location($item3));
        // Check if location has item1 donc que la réaction a bien été effectuée.
        $this->assertTrue($location->has_item_location($item4));
        // Check if location has item donc que la condition est vraie.

        $emptyaction = new Action('action', []);
        $result = $emptyaction->do_conditions();
        $this->assertEquals($result, []);

        $characterreaction4 = new Character_Reaction('Michel mange la poire', [], [], [$item2], [], $character, null);
        $characterreaction5 = new Character_Reaction('Michel ramasse la poire', [], [], [], [$item2], $character, null);

        $unvalidcondition = new Leaf_Condition($character, $item3, 'a', [], [], [$characterreaction4]);
        $validecondition = new Leaf_Condition($character, $item2, 'a', [], [], [$characterreaction4]);
        $validecondition2 = new Leaf_Condition($character, $item2, "a pas", [], [], [$characterreaction5]);
        $unvalidconditionwithreactions = new Node_Condition($unvalidcondition, null,
            "&", [$characterreaction4]);
        $unvalidconditionwithreactions2 = new Node_Condition($unvalidcondition, $validecondition,
            "&", [$characterreaction4]);

        $validconditionwithreaction = new Node_Condition($validecondition, null,
            "|", [$characterreaction4]);
        $validconditionwithreaction2 = new Node_Condition($unvalidcondition, $validecondition2,
            "|", [$characterreaction5]);

        // Test & et | Node condition.

        $action1 = new Action('action', [$unvalidconditionwithreactions]);
        // Test the do_condition method.
        $result = $action1->do_conditions();
        // Check le personnage a gardé la poire.
        $this->assertTrue($character->has_item_character($item2));

        $action2 = new Action('action', [$unvalidconditionwithreactions2]);
        $result = $action2->do_conditions();
        // Check le personnage a gardé la poire.
        $this->assertTrue($character->has_item_character($item2));

        $action3 = new Action('action', [$unvalidconditionwithreactions, $unvalidconditionwithreactions2]);
        $result = $action3->do_conditions();
        // Check le personnage a gardé la poire.
        $this->assertTrue($character->has_item_character($item2));

        $action4 = new Action('action', [$validconditionwithreaction]);
        $result = $action4->do_conditions();
        // Check le personnage a mangé la poire.
        $this->assertFalse($character->has_item_character($item2));

        $action5 = new Action('action', [$validconditionwithreaction2, $unvalidconditionwithreactions2]);
        $result = $action5->do_conditions();
        // Check le personnage a ramassé la poire.
        $this->assertTrue($character->has_item_character($item2));

        $action6 = new Action('action', [$validconditionwithreaction]);
        $result = $action6->do_conditions();
        // Check le personnage a mangé la poire.
        $this->assertFalse($character->has_item_character($item2));
    }

}

