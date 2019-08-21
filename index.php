<?php

require __DIR__ . '/vendor/autoload.php';

use App\Mazegenerator\Maze;

$maze = new Maze(35,35,4);

$maze->moveAwhile();

$maze->showMaze();
