<?php

namespace ArtisanSdk\Machine\Tests\Regression;

use ArtisanSdk\Machine\Regression\Base;
use ArtisanSdk\Machine\Tests\TestCase;
use stdClass;

class BaseTest extends TestCase
{
    /**
     * Test that a regression algorithm can be cast to a string.
     */
    public function test_casts_to_string()
    {
        $json = '{"name":"ArtisanSdk\Machine\Regression\Base"}';
        $regression = new TestBase();

        $this->assertSame($json, stripslashes((string) $regression), 'When cast to a string, the model should be serialized as JSON.');
        $this->assertSame($json, stripslashes(json_encode($regression)), 'The JSON serialized format of the model should match.');
    }
}

class TestBase extends Base
{
    /**
     * Approximate the value of $y at $x.
     *
     * @param number $x
     *
     * @return float
     */
    public function approximate($x): float
    {
        return (float) 1;
    }

    /**
     * Load from model representation.
     *
     * @param \stdClass $model
     *
     * @return \ArtisanSdk\Machine\Regression\Base
     */
    public static function fromModel(stdClass $model): Base
    {
        static::assertSameModel($model);

        return new static();
    }
}
