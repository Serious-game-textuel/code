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
require_once 'src/interfaces/Game_Interface.php';
require_once 'src/interfaces/App_Interface.php';
require_once 'src/classes/App.php';
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Environment\Console;

class AppTest extends TestCase {
    /**
     * vérifie que le constructeur initialise correctement les propriétés
     */
    public function testGetSetGame() {
        $game1 = $this->createMock(Game_Interface::class);
        $game2 = $this->createMock(Game_Interface::class);

        $app = new App($game1, $game2);

        $this->assertSame($game1, $app->get_game());

        $app->set_game($game2);
        $this->assertSame($game2, $app->get_game());
    }
    /**
     * vérifie que le constructeur initialise correctement les propriétés
     */
    public function testGetSetSave() {
        $game1 = $this->createMock(Game_Interface::class);
        $game2 = $this->createMock(Game_Interface::class);

        $app = new App($game1, $game2);

        $this->assertSame($game2, $app->get_save());

        $app->set_save($game1);
        $this->assertSame($game1, $app->get_save());
    }
}


