<?php

namespace ArtisanSdk\Machine\Tests\Regression;

use ArtisanSdk\Machine\Regression\Power;
use ArtisanSdk\Machine\Tests\TestCase;
use InvalidArgumentException;

class PowerTest extends TestCase
{
    /**
     * Test that the PR regresses when the model is made.
     */
    public function test_algorithm_creates_a_regression_model_when_made()
    {
        $inputs = [17.6, 26, 31.9, 38.9, 45.8, 51.2, 58.1, 64.7, 66.7, 80.8, 82.9];
        $outputs = [159.9, 206.9, 236.8, 269.9, 300.6, 323.6, 351.7, 377.6, 384.1, 437.2, 444.7];
        $pr = Power::make($inputs, $outputs);
        $score = $pr->score($inputs, $outputs);

        // Test the regression equation...
        $this->assertInstanceOf(Power::class, $pr, 'A PR instance of '.Power::class.' class should be made.');
        $this->assertSame(24.13, $pr->coefficient(2), 'The A coefficient should be approximately 24.13.');
        $this->assertSame(0.66, $pr->exponent(2), 'The B exponent should be approximately 0.66.');
        $this->assertSame('f(x) = 24.13x^0.66', $pr->equation(2), 'The equation form for f(x) to 2 digits of precision should match.');

        // Test the predictive algorithm...
        $this->assertSame($pr->getY(20), $pr->predict(20)->current(), 'The predicted value for x = 20 should be the same as the computed value of y at x = 20.');
        $this->assertSame(20.0, $pr->getX($pr->getY(20), 0), 'Getting x from y when y was derived from a known x should match the known x.');
        $this->assertSame(174.0, $pr->getY(20, 0), 'The value of y at x = 20 should be approximately 174.');
        $this->assertSame(227.0, $pr->getY(30, 0), 'The value of y at x = 30 should be approximately 227.');
        $this->assertSame($pr->coefficient() * pow(20, $pr->exponent()), $pr->getY(20), 'The f(x) = Ax^B should match the expected value of y for x = 20.');
        $this->assertSame($pr->coefficient() * pow(30, $pr->exponent()), $pr->getY(30), 'The f(x) = Ax^B should match the expected value of y for x = 30.');

        // Test the scoring properties...
        $this->assertSame(0.999993, $score->r(6), 'The r score should be approximately 0.999993.');
        $this->assertSame(0.999987, $score->r2(6), 'The r^2 score should be approximately 0.999987.');
        $this->assertSame(pow($score->r(), 2), $score->r2(), 'The r^2 score should the square of r.');
        $this->assertSame(0.003, $score->chi2(3), 'The chi^2 score should be approximately 0.003.');
        $this->assertSame(0.0003, $score->rmsd(4), 'The RMSD score should be approximately 0.0003.');
    }

    /**
     * Test that different sized input and output sets throws an exception.
     */
    public function test_exception_for_different_input_and_output_sizes()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$x and $y must be arrays of the same length.');

        $pr = Power::make([0, 1, 2], [0, 1]);
    }

    /**
     * Test that an exported model can be loaded.
     */
    public function test_load_from_export()
    {
        $json = (new Power(1, 2))->toJson();

        $this->assertSame('{"name":"ArtisanSdk\Machine\Regression\Power","coefficient":1,"exponent":2,"equation":"f(x) = x^2"}', stripslashes($json), 'The JSON serialized format of the model should match.');

        $model = json_decode($json);

        $this->assertSame(['name', 'coefficient', 'exponent', 'equation'], array_keys((array) $model), 'The model should have the following properties: name, coefficient, coefficient, equation.');
        $this->assertSame(Power::class, $model->name, 'The name of the model should be the same as the '.Power::class.' class.');
        $this->assertSame(1, $model->coefficient, 'The A coefficient should be 1.');
        $this->assertSame(2, $model->exponent, 'The B exponent should be 2.');
        $this->assertSame('f(x) = x^2', $model->equation, 'The equation form for f(x) should match.');

        $pr = Power::fromJson($json);

        $this->assertSame(1.0, $pr->coefficient(), 'The A coefficient should be approximately 1.0.');
        $this->assertSame(2.0, $pr->exponent(), 'The B exponent should be approximately 2.0.');
        $this->assertSame('f(x) = x^2', $pr->equation(2), 'The equation form for f(x) should match.');
    }

    /**
     * Test that a invalid model throws an exception when loaded.
     */
    public function test_invalid_load_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Model is not a '.Power::class.' model.');

        $pr = Power::fromModel((object) [
            'name'        => 'Foo\Bar',
            'coefficient' => 1,
            'exponent'    => 1,
        ]);
    }
}
