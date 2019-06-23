<?php


namespace App\Mazegenerator;


class MazePoint2D
{
    public $x;
    public $y;

    /**
     * Predefined directions
     *
     * @var
     */
    private static $N;
    private static $S;
    private static $W;
    private static $E;

    private static $NE;
    private static $NW;
    private static $SE;
    private static $SW;

    /**
     * Predefined step length
     *
     * @var int
     */
    public static $step_length;

    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function addPoint(MazePoint2D $vector)
    {
        return new self($this->x + $vector->x, $this->y + $vector->y);
    }

    public function multiplyAdd(MazePoint2D $vector)
    {
        $this->x += $vector->x;
        $this->y += $vector->y;

        return $this;
    }

    public function getNormal()
    {
        return new self($this->x / self::$step_length, $this->y / self::$step_length);
    }

    public function getInverse()
    {
        $new_maze_point = null;

        if ($this->y == 0) {
            if ($this->x == -1 * self::$step_length) {
                $new_maze_point = self::S();
            } elseif ($this->x == 1 * self::$step_length) {
                $new_maze_point = self::N();
            }
        } elseif ($this->x == 0) {
            if ($this->y == -1 * self::$step_length) {
                $new_maze_point = self::E();
            } elseif ($this->y == 1 * self::$step_length) {
                $new_maze_point = self::W();
            }
        }

        return $new_maze_point;
    }

    public static function N()
    {
        if (self::$N == null) {
            self::$N = new MazePoint2D(-1 * self::$step_length, 0);
        }

        return self::$N;
    }

    public static function S()
    {
        if (self::$S == null) {
            self::$S = new MazePoint2D(1 * self::$step_length, 0);
        }

        return self::$S;
    }

    public static function W()
    {
        if (self::$W == null) {
            self::$W = new MazePoint2D(0, -1 * self::$step_length);
        }

        return self::$W;
    }

    public static function E()
    {
        if (self::$E == null) {
            self::$E = new MazePoint2D(0, 1 * self::$step_length);
        }

        return self::$E;
    }

    public static function NE()
    {
        if (self::$NE == null) {
            self::$NE = new MazePoint2D(-1 * self::$step_length, 1 * self::$step_length);
        }

        return self::$NE;
    }

    public static function NW()
    {
        if (self::$NW == null) {
            self::$NW = new MazePoint2D(-1 * self::$step_length, -1 * self::$step_length);
        }

        return self::$NW;
    }

    public static function SE()
    {
        if (self::$SE == null) {
            self::$SE = new MazePoint2D(1 * self::$step_length, 1 * self::$step_length);
        }

        return self::$SE;
    }

    public static function SW()
    {
        if (self::$SW == null) {
            self::$SW = new MazePoint2D(1 * self::$step_length, -1 * self::$step_length);
        }

        return self::$SW;
    }
}