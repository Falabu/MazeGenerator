<?php


namespace App\MazeGenerator;


class MazeRoom
{
    private $number_of_doors;
    public $size_x;
    public $size_y;
    /**
     * @var MazePoint2D
     */
    private $coordinate;


    public function __construct(int $size_x, int $size_y, MazePoint2D $coordinate)
    {
        $this->size_x = $size_x;
        $this->size_y = $size_y;
        $this->coordinate = $coordinate;
    }

    public function getCoordinate(): MazePoint2D
    {
        return $this->coordinate;
    }

    public function getBoundaries()
    {
        $array = array();

        for ($x = 0; $x < $this->size_x; $x++) {
            for ($y = 0; $y < $this->size_y; $y++) {
                $new_pint = new MazePoint2D($x, $y);

                if ($x == 0 && $y !== 0 && $y !== $this->size_y - 1) {
                    $new_array['direction'] = MazePoint2D::N();
                    $new_array['coordinate'] = $this->coordinate->addPoint($new_pint);

                    $array['N'][] = $new_array;
                }

                if ($x == $this->size_x - 1 && $y !== 0 && $y !== $this->size_y - 1) {
                    $new_array['direction'] = MazePoint2D::S();
                    $new_array['coordinate'] = $this->coordinate->addPoint($new_pint);

                    $array['S'][] = $new_array;
                }

                if ($y == 0 && $x !== 0 && $x !== $this->size_x - 1) {
                    $new_array['direction'] = MazePoint2D::W();
                    $new_array['coordinate'] = $this->coordinate->addPoint($new_pint);

                    $array['W'][] = $new_array;
                }

                if ($y == $this->size_y - 1 && $x !== 0 && $x !== $this->size_x - 1) {
                    $new_array['direction'] = MazePoint2D::E();
                    $new_array['coordinate'] = $this->coordinate->addPoint($new_pint);

                    $array['E'][] = $new_array;
                }
            }
        }

        return $array;
    }
}