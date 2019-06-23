<?php


namespace App\MazeGenerator;


class MazeCell
{
    private $backward_move;

    private $in_maze;

    private $is_room;

    private $is_door;

    private $room_id;

    public function __construct()
    {
        $this->in_maze = false;
        $this->is_room = false;
        $this->is_door = false;
    }

    public function setRoom(MazeRoom $mazeRoom)
    {
        $this->is_room = true;
        $this->room_id = &$mazeRoom;
    }

    public function isDoor(){
        return $this->is_door;
    }

    public function isRoom()
    {
        return $this->is_room;
    }

    public function getRoom(): ?MazeRoom
    {
        return $this->room_id;
    }

    public function addBackwardMove(MazePoint2D $point)
    {
        $this->backward_move = $point;
    }

    public function getBackMove(): ?MazePoint2D
    {
        return $this->backward_move;
    }

    public function addToMaze()
    {
        $this->in_maze = true;
    }

    public function isInMaze()
    {
        return $this->in_maze;
    }

    public function setDoor(){
        $this->is_door = true;
    }
}