<?php

require __DIR__ . '/vendor/autoload.php';

use App\Mazegenerator\Maze;

$maze = new Maze(100,100,3);

$maze->moveAwhile();

$maze->showMaze();
