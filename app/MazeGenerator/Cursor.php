<?php

namespace App\Mazegenerator;

class Cursor
{

    /**
     * @var MazePoint2D
     */
    private $cursor_position;

    /**
     * Bound vectors, these are the max values fo the position vectors
     *
     * @var int
     */
    private $bound_x;
    private $bound_y;

    public function __construct(int $bound_x, int $bound_y, $x = null, $y = null)
    {
        $temp_x = $x;
        $temp_y = $y;

        $this->bound_x = $bound_x - 1;
        $this->bound_y = $bound_y - 1;

        if (null === $temp_x) {
            $temp_x = random_int(0, $this->bound_x);
        }
        if (null === $temp_y) {
            $temp_y = random_int(0, $this->bound_y);
        }

        $this->cursor_position = new MazePoint2D($temp_x, $temp_y);
    }

    public function generateNewStartPosition()
    {
        $this->cursor_position = new MazePoint2D(random_int(0, $this->bound_x), random_int(0, $this->bound_y));
    }

    public function changePosition(MazePoint2D $point)
    {
        $temp = $this->cursor_position->addPoint($point);

        if ($temp->x >= 0 && $temp->x <= $this->bound_x) {
            $this->cursor_position->x = $temp->x;
        }

        if ($temp->y >= 0 && $temp->y <= $this->bound_y) {
            $this->cursor_position->y = $temp->y;
        }
    }

    public function getPosition()
    {
        return $this->cursor_position;
    }

    public function getNewPosition()
    {
        return new MazePoint2D($this->cursor_position->x, $this->cursor_position->y);
    }
}