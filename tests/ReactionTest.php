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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Reaction_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Character_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Location_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Reaction.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Character_Reaction.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Location_Reaction.php');
use PHPUnit\Framework\TestCase;

class ReactionTest extends TestCase {
    /**
     * vérifie le comportement de la classe Character_Reaction
     */
    public function testcharacterreaction() {
        $character = $this->createMock(Character_Interface::class);
        $newlocation = $this->createMock(Location_Interface::class);

        $reaction = new Character_Reaction(1, "Description",
        ['old_status'], ['new_status'], ['old_item'], ['new_item'], $character, $newlocation);

        $this->assertInstanceOf(Character_Reaction::class, $reaction);
        $this->assertEquals($character, $reaction->get_character());
        $this->assertEquals($newlocation, $reaction->get_new_location());
    }
    /**
     * vérifie le comportement de la classe Location_Reaction
     */
    public function testlocationreaction() {
        $location = $this->createMock(Location_Interface::class);

        $reaction = new Location_Reaction(2, "Description", ['old_status'], ['new_status'], ['old_item'], ['new_item'], $location);

        $this->assertInstanceOf(Location_Reaction::class, $reaction);
        $this->assertEquals($location, $reaction->get_location());
    }

}