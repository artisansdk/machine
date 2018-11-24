<?php

namespace ArtisanSdk\Machine\Tests\Regression;

use ArtisanSdk\Machine\Regression\Score;
use ArtisanSdk\Machine\Tests\TestCase;
use InvalidArgumentException;

class ScoreTest extends TestCase
{
    /**
     * Test that a invalid score property throws an exception when loaded.
     */
    public function test_invalid_load_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Property $foo is not a property on '.Score::class.'.');

        $score = new Score(0, 0, 0, 0);
        $foo = $score->foo();
    }
}
