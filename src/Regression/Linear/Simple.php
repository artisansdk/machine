<?php

namespace ArtisanSdk\Machine\Regression\Linear;

use ArtisanSdk\Machine\Regression\Base;
use stdClass;

/**
 * Simple Linear Regression (SLR) Algorithm.
 *
 * @example (new Simple($slope, $intercept))->predict($x)
 *          Simple::make($x, $y)->predict($x)
 *          Simple::make($x, $y)->toJson()
 *          Simple::fromJson($json)->predict($x)
 */
class Simple extends Base
{
    /**
     * The rise-over-run slope of the function.
     *
     * @var float
     */
    protected $slope = 0;

    /**
     * The y-intercept of the function.
     *
     * @var float
     */
    protected $intercept = 0;

    /**
     * Run a new linear regression.
     *
     * @param number $slope
     * @param number $intercept
     *
     * @return \ArtisanSdk\Machine\Regression\Linear\Simple
     */
    public function __construct($slope = 0, $intercept = 0)
    {
        $this->slope = (float) $slope;
        $this->intercept = (float) $intercept;
    }

    /**
     * Make a new simple linear regression.
     *
     * @param number|array|\SplFixedArray $x
     * @param number|array|\SplFixedArray $y
     *
     * @return \ArtisanSdk\Machine\Regression\Base
     */
    public static function make($x, $y): Base
    {
        $slr = new static();

        return $slr($x, $y);
    }

    /**
     * Load from model representation.
     *
     * @param \stdClass $model
     *
     * @return \ArtisanSdk\Machine\Regression\Linear\Simple
     */
    public static function fromModel(stdClass $model): Base
    {
        static::assertSameModel($model);

        return new static($model->slope, $model->intercept);
    }

    /**
     * Run the regression.
     *
     * @param number|array|\SplFixedArray $x
     * @param number|array|\SplFixedArray $y
     *
     * @return \ArtisanSdk\Machine\Regression\Base
     */
    public function __invoke($x, $y): Base
    {
        $x = $this->mixedToFixedArray($x);
        $y = $this->mixedToFixedArray($y);
        $this->assertSameSize($x, $y);

        $xSum = 0;
        $ySum = 0;
        $xSquared = 0;
        $xY = 0;

        $size = $x->getSize();

        for ($index = 0; $index < $size; ++$index) {
            $xSum += $x->offsetGet($index);
            $ySum += $y->offsetGet($index);
            $xSquared += pow($x->offsetGet($index), 2);
            $xY += $x->offsetGet($index) * $y->offsetGet($index);
        }

        $numerator = ($size * $xY) - ($xSum * $ySum);
        $slope = $numerator / (($size * $xSquared) - pow($xSum, 2));
        $intercept = ((1 / $size) * $ySum) - ($slope * (1 / $size) * $xSum);

        return new static($slope, $intercept);
    }

    /**
     * Approximate the value of $y at $x.
     *
     * @param number $x
     *
     * @return float
     */
    protected function approximate($x): float
    {
        return $this->getY($x);
    }

    /**
     * Compute the value of $x at the value of $y.
     *
     * @param number $y
     * @param int    $precision
     *
     * @return float
     */
    public function getX($y, int $precision = null): float
    {
        return $this->toPrecision(($y - $this->intercept) / $this->slope, $precision);
    }

    /**
     * Compute the value of $y at the value of $x.
     *
     * @param number $x
     *
     * @return float
     */
    public function getY($x, int $precision = null): float
    {
        return $this->toPrecision($this->slope * $x + $this->intercept, $precision);
    }

    /**
     * Get the coefficients of the function.
     *
     * @param int $precision
     *
     * @return array
     */
    public function coefficients(int $precision = null): array
    {
        return [
            $this->intercept($precision), // x0
            $this->slope($precision), // x1
        ];
    }

    /**
     * Get the intercept of the function.
     *
     * @return float
     */
    public function intercept(int $precision = null): float
    {
        return $this->toPrecision($this->intercept, $precision);
    }

    /**
     * Get the slope of the function.
     *
     * @return float
     */
    public function slope(int $precision = null): float
    {
        return $this->toPrecision($this->slope, $precision);
    }

    /**
     * Convert the algorithm to an equation string.
     *
     * @param int $precision
     *
     * @return string
     */
    public function equation(int $precision = null): string
    {
        $equation = 'f(x) = ';

        if ($this->slope === (float) 0) {
            return $equation.$this->preciseString($this->intercept, $precision);
        }

        $coefficient = $this->preciseString($this->slope, $precision);
        $equation .= sprintf('%sx', '1' === $coefficient ? '' : $coefficient);

        if ($this->intercept !== (float) 0) {
            $absIntercept = abs($this->intercept);
            $operator = $absIntercept === $this->intercept ? '+' : '-';
            $equation .= sprintf(' %s %s', $operator, $this->preciseString($absIntercept, $precision));
        }

        return $equation;
    }

    /**
     * Convert to an array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name'         => __CLASS__,
            'slope'        => $this->slope(),
            'intercept'    => $this->intercept(),
            'coefficients' => $this->coefficients(),
            'equation'     => $this->equation(),
        ];
    }
}
