<?php

namespace App\Mazegenerator;

class Maze
{
    private $maze = array();

    private $bound_x;

    private $bound_y;
    /**
     * @var MazeRoom
     */
    private $rooms;

    private $dead_end_coordinates;

    private $no_more_move;
    /**
     * @var Cursor
     */
    private $cursor;

    public function __construct(int $with, int $height, int $gap = 2)
    {
        MazePoint2D::$step_length = $gap;

        $this->no_more_move = false;

        $this->bound_x = $with;
        $this->bound_y = $height;

        $this->cursor = new Cursor($with, $height);

        $this->createGrid();

        $this->selectCell($this->cursor->getPosition())->addToMaze();
    }

    public function createGrid()
    {
        for ($x = 0; $x < $this->bound_x; $x++) {
            for ($y = 0; $y < $this->bound_y; $y++) {
                $this->maze[$x][$y] = new MazeCell();
            }
        }
    }

    public function generateMaze()
    {
        if ($this->selectCell($this->cursor->getPosition())->isRoom()) {
            $this->cursor->generateNewStartPosition();
        }

        $cardinal_directions = array(MazePoint2D::E(), MazePoint2D::N(), MazePoint2D::S(), MazePoint2D::W());
        $ordinal_direction = array(MazePoint2D::NE(), MazePoint2D::NW(), MazePoint2D::SE(), MazePoint2D::SW());

        $position_keys = array();

        //Decide which direction is free to go
        foreach ($cardinal_directions as $key => $direction) {
            $next = $this->cursor->getPosition()->addPoint($direction);
            $next_cell = $this->selectCell($next);

            //Decide if there is a room next to the next move
            $room_cell = false;
            foreach (array_merge($cardinal_directions, $ordinal_direction) as $direct) {
                $next_next = $next->addPoint($direct->getNormal());
                $next_next_cell = $this->selectCell($next_next);

                if ($next_next_cell && $next_next_cell->isInMaze()) {
                    $room_cell = true;
                }
            }

            if ($next_cell && !$next_cell->isInMaze() && $room_cell == false) {
                $position_keys[] = $key;
            }
        }

        //If there is possible direction we draw the maze
        if (count($position_keys) > 0) {
            $random_key = $position_keys[array_rand($position_keys)];
            $selected_direction = $cardinal_directions[$random_key];

            $backward_move = $selected_direction->getInverse();

            //Fill the gap between two points
            $gap_from_pos = $this->cursor->getNewPosition();
            for ($i = 0; $i < MazePoint2D::$step_length; $i++) {
                $gap_position = $gap_from_pos->multiplyAdd($selected_direction->getNormal());
                $this->selectCell($gap_position)->addToMaze();
            }

            $this->cursor->changePosition($selected_direction);

            $this->selectCell($this->cursor->getPosition())->addToMaze();
            $this->selectCell($this->cursor->getPosition())->addBackwardMove($backward_move);

        } else {
            $current_cell = $this->selectCell($this->cursor->getPosition());

            if ($current_cell->getBackMove()) {
                $this->cursor->changePosition($current_cell->getBackMove());
            } else {
                $this->no_more_move = true;
            }
        }
    }

    public function createDoorways()
    {
        $max_doors = random_int(1, 3);

        foreach ($this->rooms as $room) {
            $room_boundaries = $room->getBoundaries();
            $doors_ready = false;

            do {
                $random_direct = ['W', 'E', 'S', 'N'];
                $max_door_number = 0;

                while ($max_door_number < $max_doors) {
                    $good_direction = false;
                    shuffle($random_direct);
                    $selected = array_pop($random_direct);

                    $random_key = array_rand($room_boundaries[$selected]);

                    if (isset($room_boundaries[$selected])) {
                        $coordinate = $room_boundaries[$selected][$random_key]['coordinate'] ?? null;
                        $direction = $room_boundaries[$selected][$random_key]['direction'] ?? null;
                        $distance = 0;

                        //Decide which direction have reachable maze floor
                        $new_coordinate = new MazePoint2D($coordinate->x, $coordinate->y);
                        for ($i = 0; $i <= MazePoint2D::$step_length; $i++) {
                            $next_pos = $new_coordinate->multiplyAdd($direction->getNormal());
                            $cell = $this->selectCell($next_pos);

                            if ($cell && $cell->isInMaze()) {
                                $good_direction = true;
                                $distance = $i;
                            }
                        }

                        //Draw the doorway
                        if ($good_direction) {
                            $gap_from_pos = $coordinate;

                            for ($j = 0; $j < $distance; $j++) {
                                $gap_position = $gap_from_pos->multiplyAdd($direction->getNormal());
                                $cell = $this->selectCell($gap_position);
                                $cell->addToMaze();
                                if ($j == 0) {
                                    $cell->setDoor();
                                }
                            }

                            $doors_ready = true;
                        }

                        $max_door_number++;
                    }
                }
            } while ($doors_ready == false);

        }
    }

    public function moveAwhile()
    {
        for ($i = 0; $i < 6; $i++) {
            $this->rooms[] = $this->createRoom();
        }

        while ($this->no_more_move == false) {
            $this->generateMaze();
        }

        $this->createDoorways();


        $this->findDeadEnds();
        $dead_end_length = count($this->dead_end_coordinates);

        shuffle($this->dead_end_coordinates);

        foreach (array_slice($this->dead_end_coordinates, 0, $dead_end_length / 5) as $cord) {
            $this->extendMaze($cord);
        }

        $this->findDeadEnds();

        foreach ($this->dead_end_coordinates as $cord) {
            $this->shrinkDeadEnd($cord);
        }
    }

    private function extendMaze(MazePoint2D $from)
    {
        if ($this->selectCell($from)->getBackMove()) {
            $next_cell_pos = $from->addPoint($this->selectCell($from)->getBackMove()->getInverse());
            $next_cell = $this->selectCell($next_cell_pos);

            $next_cell_pos_normal = $this->selectCell($from)->getBackMove()->getInverse()->getNormal();
            if ($next_cell && $next_cell->isInMaze()) {
                for ($i = 0; $i < MazePoint2D::$step_length; $i++) {
                    $current_cell = $this->selectCell($from);
                    $current_cell->addToMaze();

                    $from->multiplyAdd($next_cell_pos_normal);
                }
            }
        }
    }

    private function shrinkDeadEnd(MazePoint2D $from)
    {
        do {
            $ready = true;

            $cell_backward_move = $this->selectCell($from)->getBackMove();
            if ($cell_backward_move) {
                $cell_backward_move_normal = $cell_backward_move->getNormal();

                for ($i = 0; $i < MazePoint2D::$step_length; $i++) {
                    if ($this->countWalls($from) === 3) {
                        $this->selectCell($from)->removeFromEnd();
                        $this->selectCell($from)->removeFromMaze();
                        $from->multiplyAdd($cell_backward_move_normal);

                        $ready = false;
                    }
                }
            }

        } while ($ready == false);
    }

    private function countWalls(MazePoint2D $current_point)
    {
        $cardinal_directions = array(MazePoint2D::E(), MazePoint2D::N(), MazePoint2D::S(), MazePoint2D::W());
        $walls = 0;

        foreach ($cardinal_directions as $direction) {
            $new_point = $current_point->addPoint($direction->getNormal());
            $selected_cell = $this->selectCell($new_point);

            if (null == $selected_cell) {
                $walls++;
            }

            if (null !== $selected_cell && !$selected_cell->isInMaze()) {
                $walls++;
            }
        }

        return $walls;
    }

    private function findDeadEnds()
    {
        $this->dead_end_coordinates = array();

        foreach ($this->maze as $x_index => $x_cell) {
            foreach ($x_cell as $y_index => $y_cell) {
                $current_point = new MazePoint2D($x_index, $y_index);
                $current_cell = $this->selectCell($current_point);

                if ($current_cell->isInMaze()) {
                    $walls = $this->countWalls($current_point);

                    if ($walls == 3) {
                        $this->selectCell($current_point)->setEnd();
                        $this->dead_end_coordinates[] = $current_point;
                    }
                }
            }
        }
    }

    private function selectCell(MazePoint2D $point): ?MazeCell
    {
        $maze_cell = null;

        if (isset($this->maze[$point->x][$point->y])) {
            $maze_cell = $this->maze[$point->x][$point->y];
        }

        return $maze_cell;
    }

    private function createRoom(): ?MazeRoom
    {
        $room = null;
        $room_min_distance = 5;

        $room_min_size_x = 3;
        $room_max_size_x = 7;

        $room_min_size_y = 3;
        $room_max_size_y = 7;

        $room_ready = false;

        while ($room_ready == false) {
            $occupied = false;

            $size_x = random_int($room_min_size_x, $room_max_size_x);
            $size_y = random_int($room_min_size_y, $room_max_size_y);

            $pos_x = random_int(0 + $size_x, $this->bound_x - $size_x);
            $pos_y = random_int(0 + $size_y, $this->bound_y - $size_y);

            //Decide if the spot is occupied by other room
            for ($i = -$room_min_distance; $i < $size_x + $room_min_distance; $i++) {
                for ($j = -$room_min_distance; $j < $size_y + $room_min_distance; $j++) {
                    $point = new MazePoint2D($i + $pos_x, $j + $pos_y);
                    $cell = $this->selectCell($point);
                    if ($cell && $cell->isRoom()) {
                        $occupied = true;

                    }
                }
            }
            //If spot is free draw the room
            if ($occupied == false) {
                $room = new MazeRoom($size_x, $size_y, new MazePoint2D($pos_x, $pos_y));

                for ($i = 0; $i < $size_x; $i++) {
                    for ($j = 0; $j < $size_y; $j++) {
                        $point = new MazePoint2D($i + $pos_x, $j + $pos_y);
                        $cell = $this->selectCell($point);
                        $cell->addToMaze();
                        $cell->setRoom($room);
                    }
                }

                $room_ready = true;
            }
        }

        return $room;
    }


    public function showMaze()
    {
        echo "<body style='background-color: black'>";
        echo 'Maze<br>';
        echo '<table cellspacing="0" cellpadding="0">';
        foreach ($this->maze as $row) {
            echo '<tr>';
            foreach ($row as $column) {
                if ($column->isInMaze()) {
                    $color = 'white';
                } else {
                    $color = 'black';
                }
                if ($column->isRoom()) {
                    $color = 'red';
                }
                if ($column->isDoor()) {
                    $color = 'green';
                }
                if ($column->isEnd()) {
                    $color = 'purple';
                }
                if ($column->deleted()) {
                    $color = 'brown';
                }
                echo "<th style='padding: 0; width: 10px; height: 10px; background-color:" . $color . "'></th>";
            }
            echo '</tr>';
        }
        echo '</table>';
        echo "</body>";
    }
}