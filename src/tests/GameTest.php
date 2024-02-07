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
require_once 'src/interfaces/App_Interface.php';
require_once 'src/interfaces/Location_Interface.php';
require_once 'src/interfaces/Entity_Interface.php';
require_once 'src/interfaces/Action_Interface.php';
require_once 'src/classes/App.php';
require_once 'src/classes/Game.php';
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Environment\Console;

class GameTest extends TestCase {
    /**
     * vérifie si la méthode getinstance retourne bien une instance de Game
     */
    public function testGetInstance() {
        $game1 = Game::getinstance();
        $game2 = Game::getinstance();

        $this->assertInstanceOf(Game::class, $game1);
        $this->assertSame($game1, $game2); // Should return the same instance
    }
    /**
     * vérifie si les méthodes get_id et set_id fonctionnent correctement en   récupérant et en définissant l'ID du jeu
     */
    public function testGetSetId() {
        $game = new Game(0, 0, [], [], new DateTime(), Language::FR, $this->createMock(Location_Interface::class));

        $this->assertEquals(0, $game->get_id());

        $game->set_id(5);
        $this->assertEquals(5, $game->get_id());
       
    }
    /**
     * vérifie si la méthode get_deaths retourne bien le nombre de morts
     */
    public function testGetDeaths() {
        $game = new Game(0, 0, [], [], new DateTime(), Language::FR, $this->createMock(Location_Interface::class));

        $this->assertEquals(0, $game->get_deaths());
    }
    /**
     * vérifie si la méthode add_deaths incrémente bien le nombre de morts
     */
    public function testAddDeaths() {
        $game = new Game(0, 0, [], [], new DateTime(), Language::FR, $this->createMock(Location_Interface::class));

        $game->add_deaths();
        $this->assertEquals(1, $game->get_deaths());
    }
    /**
     * vérifie si la méthode get_actions retourne bien la liste des actions
     */
    public function testGetActions() {
        $game = new Game(0, 0, [], [], new DateTime(), Language::FR, $this->createMock(Location_Interface::class));

        $this->assertEquals([], $game->get_actions());
    }
    /**
     * vérifie si la méthode add_action ajoute bien une action à la liste des actions
     */
    public function testAddAction() {
        $game = new Game(0, 0, [], [], new DateTime(), Language::FR, $this->createMock(Location_Interface::class));
        $action = $this->createMock(Action_Interface::class);

        $game->add_action($action);
        $this->assertEquals([$action], $game->get_actions());
    }
    /**
     * vérifie si la méthode get_visited_locations retourne bien la liste des locations visitées
     */
    public function testGetVisitedLocations() {
        $game = new Game(0, 0, [], [], new DateTime(), Language::FR, $this->createMock(Location_Interface::class));

        $this->assertEquals([], $game->get_visited_locations());
    }
    /**
     * vérifie si la méthode add_visited_location ajoute bien une location à la liste des locations visitées
     */
    public function testAddVisitedLocation() {
        $game = new Game(0, 0, [], [], new DateTime(), Language::FR, $this->createMock(Location_Interface::class));
        $location = $this->createMock(Location_Interface::class);

        $game->add_visited_location($location);
        $this->assertEquals([$location], $game->get_visited_locations());
    }
    /**
     * vérifie si la méthode get_start_time retourne bien l'heure de début du jeu
     */
    public function testGetStartTime() {
        $game = new Game(0, 0, [], [], new DateTime(), Language::FR, $this->createMock(Location_Interface::class));

        $this->assertInstanceOf(DateTime::class, $game->get_start_time());
    }
    /**
     * vérifie si la méthode set_start_time définit bien l'heure de début du jeu
     */
    public function testSetStartTime() {
        $game = new Game(0, 0, [], [], new DateTime(), Language::FR, $this->createMock(Location_Interface::class));
        $time = new DateTime();

        $game->set_start_time($time);
        $this->assertSame($time, $game->get_start_time());
    }
    /**
     * vérifie si la méthode get_language retourne bien la langue du jeu
     */
    public function testGetLanguage() {
        $game = new Game(0, 0, [], [], new DateTime(), Language::FR, $this->createMock(Location_Interface::class));

        $this->assertEquals(Language::FR, $game->get_language());
    }
    /**
     * vérifie si la méthode set_language définit bien la langue du jeu
     */
    public function testSetLanguage() {
        $game = new Game(0, 0, [], [], new DateTime(), Language::FR, $this->createMock(Location_Interface::class));
        $language = Language::EN;

        $game->set_language($language);
        $this->assertEquals($language, $game->get_language());
    }
    /**
     * vérifie si la méthode get_current_location retourne bien la location actuelle
     */
    public function testGetCurrentLocation() {
        $location = $this->createMock(Location_Interface::class);
        $game = new Game(0, 0, [], [], new DateTime(), Language::FR, $location);

        $this->assertSame($location, $game->get_current_location());
    }
    /**
     * vérifie si la méthode set_current_location définit bien la location actuelle
     */
    public function testSetCurrentLocation() {
        $location = $this->createMock(Location_Interface::class);
        $game = new Game(0, 0, [], [], new DateTime(), Language::FR, $location);
        $newLocation = $this->createMock(Location_Interface::class);

        $game->set_current_location($newLocation);
        $this->assertSame($newLocation, $game->get_current_location());
    }

}