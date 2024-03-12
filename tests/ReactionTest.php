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
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Reaction_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Character_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Location_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Reaction.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Character_Reaction.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Location_Reaction.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Util.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Item.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/App.php');
require_once($CFG->dirroot . '/mod/stg/src/Language.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Node_Condition.php');
use PHPUnit\Framework\TestCase;

/**
 * Class ReactionTest
<<<<<<< HEAD
 * @package mod_stg
=======
 * @package mod_serioustextualgame
>>>>>>> exceptions
 */
class ReactionTest extends TestCase {
    /**
     * vérifie le comportement de la classe Character_Reaction
     */
    public function testcharacterreaction() {
        global $CFG;
<<<<<<< HEAD
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
=======
        $app = new App(file_get_contents($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv'), Language::FR);
>>>>>>> exceptions
        $game = $app->get_game();
        $olditem = new Item("description1", "name1", ["status1"]);
        $newitem = new Item("description2", "name2", ["status2"]);
        $character = $this->createMock(Character_Interface::class);
        $newlocation = $this->createMock(Location_Interface::class);

        $reaction = new Character_Reaction("Description",
        ['old_status'], ['new_status'], [$olditem], [$newitem], $character, $newlocation);

        $this->assertInstanceOf(Character_Reaction::class, $reaction);
        $this->assertEquals($character, $reaction->get_character());
        $this->assertEquals($newlocation, $reaction->get_new_location());
        $this->assertEquals([$newitem], $reaction->get_new_item());
    }
    /**
     * vérifie le comportement de la classe Location_Reaction
     */
    public function testlocationreaction() {
        global $CFG;
<<<<<<< HEAD
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv', Language::FR);
=======
        $app = new App(file_get_contents($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv'), Language::FR);
>>>>>>> exceptions
        $game = $app->get_game();
        $olditem = new Item("description1", "name1", ["status1"]);
        $newitem = new Item("description2", "name2", ["status2"]);
        $location = $this->createMock(Location_Interface::class);

        $reaction = new Location_Reaction("Description", ['old_status'], ['new_status'], [$olditem], [$newitem], $location);

        $this->assertInstanceOf(Location_Reaction::class, $reaction);
        $this->assertEquals($location, $reaction->get_location());
        $this->assertEquals([$newitem], $reaction->get_new_item());

    }

}
