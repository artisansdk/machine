<?php

namespace ArtisanSdk\Machine\Regression;

use ArtisanSdk\Machine\Regression\Linear\Simple;
use SplFixedArray;
use stdClass;

/**
 * Power Regression (PR) Algorithm.
 *
 * @example (new Power($coefficient, $exponent))->predict($x)
 *          Power::make($x, $y)->predict($x)
 *          Power::make($x, $y)->toJson()
 *          Power::fromJson($json)->predict($x)
 */
class Power extends Base
{
    /**
     * The variable coefficient value.
     *
     * @var float
     */
    protected $coefficient = 0;

    /**
     * The exponent to rase the base to.
     *
     * @var float
     */
    protected $exponent = 0;

    /**
     * The SLR algorithm.
     *
     * @var \Machine\Regression\Linear\Simple
     */
    protected $slr;

    /**
     * Construct a power regression based on SLR.
     *
     * @param number                            $coefficient
     * @param number                            $exponent
     * @param \Machine\Regression\Linear\Simple $slr
     */
    public function __construct($coefficient = 0, $exponent = 1, Simple $slr = null)
    {
        $this->coefficient = (float) $coefficient;
        $this->exponent = (float) $exponent;
        $this->slr = $slr;
    }

    /**
     * Make a new power regression.
     *
     * @param number|array|\SplFixedArray       $x
     * @param number|array|\SplFixedArray       $y
     * @param \Machine\Regression\Linear\Simple $slr
     *
     * @return \ArtisanSdk\Machine\Regression\Base
     */
    public static function make($x, $y, Simple $slr = null): Base
    {
        $pr = new static();

        return $pr($x, $y);
    }

    /**
     * Load from model representation.
     *
     * @param \stdClass $model
     *
     * @return \ArtisanSdk\Machine\Regression\Power
     */
    public static function fromModel(stdClass $model): Base
    {
        static::assertSameModel($model);

        return new static($model->coefficient, $model->exponent);
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

        $size = $x->getSize();
        $xLog = new SplFixedArray($size);
        $yLog = new SplFixedArray($size);

        for ($index = 0; $index < $size; ++$index) {
            $xLog->offsetSet($index, log($x->offsetGet($index)));
            $yLog->offsetSet($index, log($y->offsetGet($index)));
        }

        $class = is_null($this->slr) ? Simple::class : get_class($this->slr);
        $slr = $class::make($xLog, $yLog);

        return new static(exp($slr->intercept()), $slr->slope(), $slr);
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
        $exponent = $this->exponent === (float) 0 ? 0 : 1 / $this->exponent;
        $base = $this->coefficient === (float) 0 ? 0 : $y / $this->coefficient;

        return $this->toPrecision(pow($base, $exponent), $precision);
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
        return $this->toPrecision($this->coefficient * pow($x, $this->exponent), $precision);
    }

    /**
     * Get the coefficient of the function.
     *
     * @return float
     */
    public function coefficient(int $precision = null): float
    {
        return $this->toPrecision($this->coefficient, $precision);
    }

    /**
     * Get the exponent of the function.
     *
     * @return float
     */
    public function exponent(int $precision = null): float
    {
        return $this->toPrecision($this->exponent, $precision);
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
        $coefficient = $this->preciseString($this->coefficient, $precision);

        return sprintf(
            'f(x) = %sx^%s',
            '1' === $coefficient ? '' : $coefficient,
            $this->preciseString($this->exponent, $precision)
        );
    }

    /**
     * Convert to an array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name'        => __CLASS__,
            'coefficient' => $this->coefficient(),
            'exponent'    => $this->exponent(),
            'equation'    => $this->equation(),
        ];
    }
}
