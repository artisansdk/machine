<?php

namespace ArtisanSdk\Machine\Regression;

use InvalidArgumentException;

/**
 * Score for Regression Algorithm.
 *
 * @example $score->chi2 or $score->chi2($precision)
 */
class Score
{
    /**
     * Read-only properties of the score.
     *
     * @var float
     */
    protected $r;
    protected $r2;
    protected $chi2;
    protected $rmsd;

    /**
     * Construct a new score.
     *
     * @param float $r
     * @param float $r2
     * @param float $chi2
     * @param float $rmsd
     */
    public function __construct(float $r, float $r2, float $chi2, float $rmsd)
    {
        $this->r = $r;
        $this->r2 = $r2;
        $this->chi2 = $chi2;
        $this->rmsd = $rmsd;
    }

    /**
     * Call the read-only property.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return float
     */
    public function __call($method, $arguments = [])
    {
        $value = $this->__get($method);
        $precision = $arguments[0] ?? null;

        return is_null($precision) ? $value : round($value, (int) $precision);
    }

    /**
     * Get the read-only property.
     *
     * @param string $key
     *
     * @throws \InvalidArgumentException
     *
     * @return float
     */
    public function __get($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }

        throw new InvalidArgumentException('Property $'.$key.' is not a property on '.__CLASS__.'.');
    }
}
