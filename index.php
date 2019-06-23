<?php

require __DIR__ . '/vendor/autoload.php';

use App\Mazegenerator\Maze;

$maze = new Maze(50,50,3);

$maze->moveAwhile();

$maze->showMaze();
