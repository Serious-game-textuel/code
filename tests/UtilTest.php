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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Util.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/App.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/Language.php');

use core_reportbuilder\external\columns\sort\get;
use PHPUnit\Framework\TestCase;

/**
 * Class UtilTest
 * @package mod_serioustextualgame
 */
class UtilTest extends TestCase {
    public function test_has_array_duplicate_string() {
        $str1 = "a";
        $str2 = "b";
        $str3 = "b";
        $this->assertFalse(Util::has_array_duplicate([$str1, $str2]));
        $this->assertTrue(Util::has_array_duplicate([$str1, $str1]));
        $this->assertTrue(Util::has_array_duplicate([$str2, $str3]));
        $this->assertFalse(Util::has_array_duplicate([$str1, null]));
        $this->assertFalse(Util::has_array_duplicate([$str1, 1]));
        $this->assertFalse(Util::has_array_duplicate([]));
    }
    public function test_has_array_duplicate_object() {
        global $CFG;
        $app = new App(file_get_contents($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv'), Language::FR);
        $item1 = new Item("une pomme", "pomme", []);
        $item2 = new Item("une poire", "poire", []);
        $this->assertFalse(Util::has_array_duplicate([$item1, $item2]));
        $this->assertTrue(Util::has_array_duplicate([$item1, $item1]));
        $this->assertFalse(Util::has_array_duplicate([$item1, null]));
        $this->assertFalse(Util::has_array_duplicate([$item1, 1]));
        $this->assertFalse(Util::has_array_duplicate([]));
    }

    public function test_check_array_string() {
        $str1 = "a";
        $str2 = "b";
        $str3 = "b";
        try {
            Util::check_array([$str1, $str2], 'string');
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->assertTrue(false);
        }
        try {
            Util::check_array([$str1, $str1], 'string');
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
        try {
            Util::check_array([$str2, $str3], 'string');
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
        try {
            Util::check_array([$str1, null], 'string');
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
        try {
            Util::check_array([$str1, 1], 'string');
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
        try {
            Util::check_array([], 'string');
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->assertTrue(false);
        }
    }
    public function test_check_array_object() {
        global $CFG;
        $app = new App(file_get_contents($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv'), Language::FR);
        $item1 = new Item("une pomme", "pomme", []);
        $item2 = new Item("une poire", "poire", []);
        try {
            Util::check_array([$item1, $item2], Item_Interface::class);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->assertTrue(false);
        }
        try {
            Util::check_array([$item1, $item1], Item_Interface::class);
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
        try {
            Util::check_array([$item1, null], Item_Interface::class);
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
        try {
            Util::check_array([$item1, 1], Item_Interface::class);
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
        try {
            Util::check_array([], Item_Interface::class);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function test_clean_array_string() {
        $str1 = "a";
        $str2 = "b";
        $str3 = "b";
        $this->assertTrue($this->array_equal(Util::clean_array([$str1], 'string'), [$str1]));
        $this->assertTrue($this->array_equal(Util::clean_array([1], 'string'), []));
        $this->assertTrue($this->array_equal(Util::clean_array([$str1, $str2], 'string'), [$str1, $str2]));
        $this->assertTrue($this->array_equal(Util::clean_array([$str1, $str1], 'string'), [$str1]));
        $this->assertTrue($this->array_equal(Util::clean_array([$str2, $str3], 'string'), [$str2]));
        $this->assertTrue($this->array_equal(Util::clean_array([$str1, null], 'string'), [$str1]));
        $this->assertTrue($this->array_equal(Util::clean_array([$str1, 1], 'string'), [$str1]));
        $this->assertTrue($this->array_equal(Util::clean_array([], 'string'), []));
    }
    public function test_clean_array_object() {
        global $CFG;
        $app = new App(file_get_contents($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv'), Language::FR);
        $item1 = new Item("une pomme", "pomme", []);
        $item2 = new Item("une poire", "poire", []);
        $this->assertTrue($this->array_equal(Util::clean_array([$item1], Item_Interface::class), [$item1]));
        $this->assertTrue($this->array_equal(Util::clean_array([1], Item_Interface::class), []));
        $this->assertTrue($this->array_equal(Util::clean_array([$item1, $item2], Item_Interface::class), [$item1, $item2]));
        $this->assertTrue($this->array_equal(Util::clean_array([$item1, $item1], Item_Interface::class), [$item1]));
        $this->assertTrue($this->array_equal(Util::clean_array([$item1, null], Item_Interface::class), [$item1]));
        $this->assertTrue($this->array_equal(Util::clean_array([$item1, 1], Item_Interface::class), [$item1]));
        $this->assertTrue($this->array_equal(Util::clean_array([], Item_Interface::class), []));
    }

    private function array_equal($a, $b) {
        sort($a);
        sort($b);
        return $a === $b;
    }
}
