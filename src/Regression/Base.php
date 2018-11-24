<?php

namespace ArtisanSdk\Machine\Regression;

use ArtisanSdk\Contract\Predictable;
use InvalidArgumentException;
use JsonSerializable;
use SplFixedArray;
use stdClass;

/**
 * Abstract Base Regression Algorithm.
 */
abstract class Base implements JsonSerializable, Predictable
{
    /**
     * Predict the target from the feature input.
     *
     * @param number|array|\SplFixedArray $x
     *
     * @return \SplFixedArray
     */
    public function predict($x): SplFixedArray
    {
        $x = $this->mixedToFixedArray($x);
        $size = $x->getSize();
        $y = new SplFixedArray($size);
        for ($index = 0; $index < $size; ++$index) {
            $y->offsetSet($index, $this->approximate($x->offsetGet($index)));
        }

        return $y;
    }

    /**
     * Approximate the value of $y at $x.
     *
     * @param number $x
     *
     * @return float
     */
    abstract protected function approximate($x): float;

    /**
     * Return the correlation coefficient of determination (r) and chi-square.
     *
     * @param number|array|\SplFixedArray $x
     * @param number|array|\SplFixedArray $y
     *
     * @return \ArtisanSdk\Machine\Regression\Score
     */
    public function score($x, $y): Score
    {
        $x = $this->mixedToFixedArray($x);
        $y = $this->mixedToFixedArray($y);
        $this->assertSameSize($x, $y);

        $size = $x->getSize();
        $y2 = new SplFixedArray($size);
        for ($index = 0; $index < $size; ++$index) {
            $y2->offsetSet($index, $this->approximate($x->offsetGet($index)));
        }

        $xSum = 0;
        $ySum = 0;
        $chi2 = 0;
        $rmsd = 0;
        $xSquared = 0;
        $ySquared = 0;
        $xY = 0;
        for ($index = 0; $index < $size; ++$index) {
            $xSum += $y2->offsetGet($index);
            $ySum += $y->offsetGet($index);
            $xSquared += pow($y2->offsetGet($index), 2);
            $ySquared += pow($y->offsetGet($index), 2);
            $xY += $y2->offsetGet($index) * $y->offsetGet($index);
            $rmsd = pow($y->offsetGet($index) - $y2->offsetGet($index), 2);
            if (0 !== $y->offsetGet($index)) {
                $chi2 += $rmsd / $y->offsetGet($index);
            }
        }

        $r = ($size * $xY - $xSum * $ySum) / sqrt(($size * $xSquared - pow($xSum, 2)) * ($size * $ySquared - pow($ySum, 2)));

        return new Score($r, pow($r, 2), $chi2, pow($rmsd, 2) / $size);
    }

    /**
     * Assert the arrays are same size.
     *
     * @param \SplFixedArray $x
     * @param \SplFixedArray $y
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public static function assertSameSize(SplFixedArray $x, SplFixedArray $y): bool
    {
        if ($x->getSize() !== $y->getSize()) {
            throw new InvalidArgumentException('$x and $y must be arrays of the same length.');
        }

        return true;
    }

    /**
     * Assert that the model is the same type.
     *
     * @param stdClass $model
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public static function assertSameModel(stdClass $model): bool
    {
        if ($model->name !== static::class) {
            throw new InvalidArgumentException('Model is not a '.static::class.' model.');
        }

        return true;
    }

    /**
     * Convert a mixed value to a fixed array.
     *
     * @param number|array|\SplFixedArray $x
     *
     * @return \SplFixedArray
     */
    protected function mixedToFixedArray($x): SplFixedArray
    {
        if (is_numeric($x)) {
            $x = [$x];
        }

        if ( ! $x instanceof SplFixedArray) {
            $x = SplFixedArray::fromArray($x, false);
        }

        return $x;
    }

    /**
     * Round to a precision.
     *
     * @param float $value
     * @param int   $precision
     *
     * @return float
     */
    protected function toPrecision(float $value, int $precision = null): float
    {
        return is_null($precision)
            ? $value
            : round($value, $precision);
    }

    /**
     * Convert a float to a precise string.
     *
     * @param float $value
     * @param int   $precision
     *
     * @return string
     */
    protected function preciseString(float $value, int $precision = null): string
    {
        $absValue = abs($value);
        $operator = $absValue === $value ? '' : '-';

        return sprintf('%s%s', $operator, (string) $this->toPrecision($absValue, $precision));
    }

    /**
     * Convert the algorithm to a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Convert the algorithm to a string.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->toJson();
    }

    /**
     * Convert to an array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => __CLASS__,
        ];
    }

    /**
     * Convert to a representation that can be JSON serialized.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert to a JSON representation.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = null): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Load model from JSON representation.
     *
     * @param string $json
     *
     * @throws \InvalidArgumentException
     *
     * @return \ArtisanSdk\Machine\Regression\Base
     */
    public static function fromJson(string $json): self
    {
        return static::fromModel(json_decode($json));
    }

    /**
     * Load from model representation.
     *
     * @param \stdClass $model
     *
     * @return \ArtisanSdk\Machine\Regression\Base
     */
    abstract public static function fromModel(stdClass $model): self;
}
