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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/App_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Location_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Entity_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Action_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/App.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Game.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Player_Character.php');
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase {
    /**
     * vérifie si la méthode getinstance retourne bien une instance de Game
     */
    public function testgetinstance() {
        $game = new Game(0, 0, 0, [], new DateTime(),
         Language::FR, $this->createMock(Location_Interface::class), $this->createMock(Player_Character::class), null, null);
        $game1 = Game::getinstance();
        $game2 = Game::getinstance();

        $this->assertInstanceOf(Game::class, $game1);
        $this->assertSame($game1, $game2); // Should return the same instance.
    }
    /**
     * vérifie si la méthode get_deaths retourne bien le nombre de morts
     */
    public function testgetdeaths() {
        $game = new Game(0, 0, 0, [], new DateTime(),
         Language::FR, $this->createMock(Location_Interface::class), $this->createMock(Player_Character::class), null, null);

        $this->assertEquals(0, $game->get_deaths());
    }
    /**
     * vérifie si la méthode add_deaths incrémente bien le nombre de morts
     */
    public function testadddeaths() {
        $game = new Game(0, 0, 0, [], new DateTime(),
         Language::FR, $this->createMock(Location_Interface::class), $this->createMock(Player_Character::class), null, null);

        $game->add_deaths();
        $this->assertEquals(1, $game->get_deaths());
    }
    /**
     * vérifie si la méthode get_actions retourne bien la liste des actions
     */
    public function testgetactions() {
        $game = new Game(0, 0, 0, [], new DateTime(),
         Language::FR, $this->createMock(Location_Interface::class), $this->createMock(Player_Character::class), null, null);

        $this->assertEquals(0, $game->get_actions());
    }
    /**
     * vérifie si la méthode add_action ajoute bien une action à la liste des actions
     */
    public function testaddaction() {
        $game = new Game(0, 0, 0, [], new DateTime(),
         Language::FR, $this->createMock(Location_Interface::class), $this->createMock(Player_Character::class), null, null);
        $action = $this->createMock(Action_Interface::class);

        $game->add_action();
        $this->assertEquals(1, $game->get_actions());
    }
    /**
     * vérifie si la méthode get_visited_locations retourne bien la liste des locations visitées
     */
    public function testgetvisitedlocations() {
        $game = new Game(0, 0, 0, [], new DateTime(),
         Language::FR, $this->createMock(Location_Interface::class), $this->createMock(Player_Character::class), null, null);

        $this->assertEquals([], $game->get_visited_locations());
    }
    /**
     * vérifie si la méthode add_visited_location ajoute bien une location à la liste des locations visitées
     */
    public function testaddvisitedlocation() {
        $game = new Game(0, 0, 0, [], new DateTime(),
         Language::FR, $this->createMock(Location_Interface::class), $this->createMock(Player_Character::class), null, null);
        $location = $this->createMock(Location_Interface::class);

        $game->add_visited_location($location);
        $this->assertEquals([$location], $game->get_visited_locations());
    }
    /**
     * vérifie si la méthode get_start_time retourne bien l'heure de début du jeu
     */
    public function testgetstarttime() {
        $game = new Game(0, 0, 0, [], new DateTime(),
         Language::FR, $this->createMock(Location_Interface::class), $this->createMock(Player_Character::class), null, null);

        $this->assertInstanceOf(DateTime::class, $game->get_start_time());
    }
    /**
     * vérifie si la méthode set_start_time définit bien l'heure de début du jeu
     */
    public function testsetstarttime() {
        $game = new Game(0, 0, 0, [], new DateTime(),
         Language::FR, $this->createMock(Location_Interface::class), $this->createMock(Player_Character::class), null, null);
        $time = new DateTime();

        $game->set_start_time($time);
        $this->assertSame($time, $game->get_start_time());
    }
    /**
     * vérifie si la méthode get_language retourne bien la langue du jeu
     */
    public function testgetlanguage() {
        $game = new Game(0, 0, 0, [], new DateTime(),
         Language::FR, $this->createMock(Location_Interface::class), $this->createMock(Player_Character::class), null, null);

        $this->assertEquals(Language::FR, $game->get_language());
    }
    /**
     * vérifie si la méthode set_language définit bien la langue du jeu
     */
    public function testsetlanguage() {
        $game = new Game(0, 0, 0, [], new DateTime(),
         Language::FR, $this->createMock(Location_Interface::class), $this->createMock(Player_Character::class), null, null);
        $language = Language::EN;

        $game->set_language($language);
        $this->assertEquals($language, $game->get_language());
    }
    /**
     * vérifie si la méthode get_current_location retourne bien la location actuelle
     */
    public function testgetcurrentlocation() {
        $location = $this->createMock(Location_Interface::class);
        $game = new Game(0, 0, 0, [], new DateTime(),
         Language::FR, $location, $this->createMock(Player_Character::class), null, null);
        $game->add_visited_location($location);
        $this->assertSame($location, $game->get_current_location());
    }
    /**
     * vérifie si la méthode set_current_location définit bien la location actuelle
     */
    public function testsetcurrentlocation() {
        $location = $this->createMock(Location_Interface::class);
        $game = new Game(0, 0, 0, [], new DateTime(),
         Language::FR, $location, $this->createMock(Player_Character::class), null, null);
        $newlocation = $this->createMock(Location_Interface::class);

        $game->set_current_location($newlocation);
        $this->assertSame($newlocation, $game->get_current_location());
    }
}
