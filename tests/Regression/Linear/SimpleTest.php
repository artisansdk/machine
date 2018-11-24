<?php

namespace ArtisanSdk\Machine\Tests\Regression\Linear;

use ArtisanSdk\Machine\Regression\Linear\Simple;
use ArtisanSdk\Machine\Tests\TestCase;
use InvalidArgumentException;

class SimpleTest extends TestCase
{
    /**
     * Test that the SLR regresses when the model is made.
     */
    public function test_algorithm_creates_a_regression_model_when_made()
    {
        $inputs = [80, 60, 10, 20, 30];
        $outputs = [20, 40, 30, 50, 60];
        $slr = Simple::make($inputs, $outputs);

        // Test the regression equation...
        $this->assertInstanceOf(Simple::class, $slr, 'A SLR instance of '.Simple::class.' class should be made.');
        $this->assertSame(-0.26, $slr->slope(2), 'The x1 coefficient which is the rise-over-run or x/y slope should be approximately -0.26.');
        $this->assertSame(50.6, $slr->intercept(1), 'The intercept should be approximately y = 50.6 at x = 0.');
        $this->assertSame([$slr->intercept(0), $slr->slope(0)], $slr->coefficients(0), 'The coefficients should be in ascending order of the powers of x: x^0, x^1, ... x^n.');
        $this->assertSame('f(x) = -0.26x + 50.59', $slr->equation(2), 'The equation form for f(x) to 2 digits of precision should match.');

        // Test the predictive algorithm...
        $this->assertSame($slr->getY(85), $slr->predict(85)->current(), 'The predicted value for x = 85 should be the same as the computed value of y at x = 85.');
        $this->assertSame(28.0, $slr->getY(85, 0), 'The value of y at x = 85 should be approximately 28.');
        $this->assertSame(85.0, $slr->getX($slr->getY(85), 0), 'Getting x from y when y was derived from a known x should match the known x.');
    }

    /**
     * Test that the scoring properties of the SLR algorithm.
     *
     * @see https://en.wikipedia.org/wiki/Simple_linear_regression#Numerical_example
     */
    public function test_algorithm_score()
    {
        $inputs = [1.47, 1.50, 1.52, 1.55, 1.57, 1.60, 1.63, 1.65, 1.68, 1.70, 1.73, 1.75, 1.78, 1.80, 1.83];
        $outputs = [52.21, 53.12, 54.48, 55.84, 57.20, 58.57, 59.93, 61.29, 63.11, 64.47, 66.28, 68.10, 69.92, 72.19, 74.46];
        $slr = Simple::make($inputs, $outputs);
        $score = $slr->score($inputs, $outputs);

        // Test the regression equation...
        $this->assertSame(61.272, $slr->slope(3), 'The x1 coefficient which is the rise-over-run or x/y slope should be approximately 61.272.');
        $this->assertSame(-39.062, $slr->intercept(3), 'The intercept should be approximately y = -39.062 at x = 0.');

        // Test the scoring properties...
        $this->assertSame(0.995, $score->r(3), 'The r score should be approximately 0.995.');
        $this->assertSame(0.989, $score->r2(3), 'The r^2 score should be approximately 0.989.');
        $this->assertSame(pow($score->r(), 2), $score->r2(), 'The r^2 score should the square of r.');
        $this->assertSame(0.118, $score->chi2(3), 'The chi^2 score should be approximately 0.118.');
        $this->assertSame(0.252, $score->rmsd(3), 'The RMSD score should be approximately 0.252.');
    }

    /**
     * Test that regression for simple equation.
     */
    public function test_algorithm_on_integer_equation()
    {
        $inputs = [0, 1, 2, 3, 4, 5];
        $outputs = [10, 8, 6, 4, 2, 0];
        $slr = Simple::make($inputs, $outputs);
        $score = $slr->score($inputs, $outputs);

        // Test the regression equation...
        $this->assertSame(-2.0, $slr->slope(), 'The x1 coefficient which is the rise-over-run or x/y slope should be approximately 2.0.');
        $this->assertSame(10.0, $slr->intercept(), 'The intercept should be approximately y = 10.0 at x = 0.');
        $this->assertSame($slr->intercept(), $slr->getY(0), 'The value of y at x = 0 should be the same as the intercept of approximately 10.0.');
        $this->assertSame(-2.0, $slr->getY(6), 'The value of y at x = 6 should be approximately -2.0.');
        $this->assertSame(12.0, $slr->getY(-1), 'The value of y at x = -1 should be approximately 12.0.');
        $this->assertSame(5.0, $slr->getY(2.5), 'The value of y at x = 2.5 should be approximately 5.0.');
        $this->assertSame(2.5, $slr->getX(5), 'The value of x at y = 5 should be approximately 2.5.');
        $this->assertSame(0.5, $slr->getX(9), 'The value of x at y = 9 should be approximately 0.5.');
        $this->assertSame(11.0, $slr->getX(-12), 'The value of x at y = -12 should be approximately 11.0.');
        $this->assertSame('f(x) = -2x + 10', $slr->equation(0), 'The equation form for f(x) should match.');

        // Test the scoring properties...
        $this->assertSame(1.0, $score->r(), 'The r score should be approximately 1.0.');
        $this->assertSame(1.0, $score->r2(), 'The r^2 score should be approximately 1.0.');
        $this->assertSame(0.0, $score->chi2(), 'The chi^2 score should be approximately 0.0.');
        $this->assertSame(0.0, $score->rmsd(), 'The RMSD score should be approximately 0.0.');
    }

    /**
     * Test that a constant function is formatted as an equation correctly.
     */
    public function test_constant_function_equation_formatting()
    {
        $inputs = [0, 1, 2, 3];
        $outputs = [2, 2, 2, 2];
        $slr = Simple::make($inputs, $outputs);

        $this->assertSame(0.0, $slr->slope(), 'The x1 coefficient which is the rise-over-run or x/y slope should be approximately 0.0.');
        $this->assertSame(2.0, $slr->intercept(), 'The intercept should be approximately y = 2.0 at x = 0.');
        $this->assertSame('f(x) = 2', $slr->equation(2), 'The equation form for f(x) should match.');
    }

    /**
     * Test that a negative intercept is formatted as an equation correctly.
     */
    public function test_negative_intercept_equation_formatting()
    {
        $inputs = [-1, 0, 1];
        $outputs = [-2, -1, 0];
        $slr = Simple::make($inputs, $outputs);

        $this->assertSame('f(x) = x - 1', $slr->equation(), 'The equation form for f(x) should match.');
    }

    /**
     * Test that different sized input and output sets throws an exception.
     */
    public function test_exception_for_different_input_and_output_sizes()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$x and $y must be arrays of the same length.');

        $slr = Simple::make([0, 1, 2], [0, 1]);
    }

    /**
     * Test that an exported model can be loaded.
     */
    public function test_load_from_export()
    {
        $json = (new Simple(1, 1))->toJson();

        $this->assertSame('{"name":"ArtisanSdk\Machine\Regression\Linear\Simple","slope":1,"intercept":1,"coefficients":[1,1],"equation":"f(x) = x + 1"}', stripslashes($json), 'The JSON serialized format of the model should match.');

        $model = json_decode($json);

        $this->assertSame(['name', 'slope', 'intercept', 'coefficients', 'equation'], array_keys((array) $model), 'The model should have the following properties: name, slope, intercept, coefficients, equation.');
        $this->assertSame(Simple::class, $model->name, 'The name of the model should be the same as the '.Simple::class.' class.');
        $this->assertSame(1, $model->slope, 'The x1 coefficient which is the rise-over-run or x/y slope should be 1.');
        $this->assertSame(1, $model->intercept, 'The intercept should be y = 1 at x = 0.');
        $this->assertSame([$model->intercept, $model->slope], $model->coefficients, 'The coefficients should be in ascending order of the powers of x: x^0, x^1, ... x^n.');
        $this->assertSame('f(x) = x + 1', $model->equation, 'The equation form for f(x) should match.');

        $slr = Simple::fromJson($json);

        $this->assertSame(1.0, $slr->slope(), 'The x1 coefficient which is the rise-over-run or x/y slope should be approximately 1.0.');
        $this->assertSame(1.0, $slr->intercept(), 'The intercept should be approximately 1.0.');
        $this->assertSame('f(x) = x + 1', $slr->equation(2), 'The equation form for f(x) should match.');
    }

    /**
     * Test that a invalid model throws an exception when loaded.
     */
    public function test_invalid_load_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Model is not a '.Simple::class.' model.');

        $slr = Simple::fromModel((object) [
            'name'      => 'Foo\Bar',
            'slope'     => 1,
            'intercept' => 1,
        ]);
    }
}
