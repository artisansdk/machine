# Machine Learning

The machine learning algorithms of the Artisan SDK packages.

## Table of Contents

- [Installation](#installation)
- [Regression Algorithms](#regression-algorithms)
- [Running the Tests](#running-the-tests)
- [Licensing](#licensing)

## Installation

The package installs into a PHP application like any other PHP package:

```bash
composer require artisansdk/machine
```

## Regression Algorithms

### Simple Linear Regression (SLR)

You use Simple Linear Regression (SLR) to derive an approximating equation of the
form `f(x) = ax + b`. The basic utility is that you provide the SLR algorithm a
data set of corresponding `x` inputs and `f(x)` outputs and let the machine derive
the best coefficients to use for `a` (slope) and `b` (intercept). The equation
can then be saved and loaded and used to predict unknown values using the approximating
equation derived.

#### Machine Basics

The following is an example of taking a known equation like `f(x) = 3x + 2` and
constructing the SLR machine with `3` for the slope and `2` for the intersect. The
equation is then used to approximate (predict) any value along the linear path.
Using `getY($x)` the `f(x)` output can be determined from the input of `x` and
using `getX($y)` the `x` input can be determined from the output of `f(x)`.

```php
use ArtisanSdk\Machine\Regression\Linear\Simple;

// Manually use the approximating equation's parameters
$machine = new Simple(3, 2);
echo $machine->slope(); // 3
echo $machine->intercept(); // 2
echo $machine->equation(); // f(x) = 3x + 2

// Get predictions
$predictions = $machine->predict([4, 0]); // SplFixedArray
echo $predictions[0]; // 14
echo $predictions[1]; // 2

// Get points along linear path
echo $machine->getY(5); // 17
echo $machine->getX(17); // 5
```

#### Saving the Machine Model

SLR training is performed on a data set but the approximating equation that is
derived is the real model for the machine. Depending on the dataset size, running
the regression can take a long time and therefore is only re-ran as often as it
needs to ingest and refit for new data points. To facilitate this, the model is
serialized as JSON and saved and reloaded as often as the machine needs to reuse
the formula. The following demonstrates persisting the model to disk, and then
reloading it again for continued use:

```php
use ArtisanSdk\Machine\Regression\Linear\Simple;

$filepath = '/path/to/data/model.json';

// Create a simple machine
$machine = new Simple(3, 2);
echo $machine->equation(); // f(x) = 3x + 2

// Save the machine to disk
$json = $machine->toJson();
file_put_contents($filepath, $json);

// Reload a machine from disk
$json = file_get_contents($filepath);
$machine = Simple::fromJson($json);

// Verify the equation is the same
echo $machine->equation(); // f(x) = 3x + 2
```

#### Running the Regression Algorithm

The following demonstrates how to let the machine learn the best coefficients to
use for the approximating equation. Given a dataset of inputs and corresponding
outputs, the machine will draw a linear path through the points and then return
a SLR machine that has the slope and intersect set and ready for predictions.

```php
use ArtisanSdk\Machine\Regression\Linear\Simple;

// Generate the approximating equation from inputs and outputs
$inputs = [80, 60, 10, 20, 30];
$outputs = [20, 40, 30, 50, 60];
$machine = Simple::make($inputs, $outputs);

// Inspect the parameters derived
echo $machine->slope(4); // -0.2647
echo $machine->intercept(2); // 50.59
echo $machine->equation(5); // f(x) = -0.26471x + 50.58824

// Use the approximating equation to make predictions
$predictions = $machine->predict([40, 20]); // SplFixedArray
echo $predictions[0]; // 40
echo $predictions[1]; // 45.294117647059
```

## Running the Tests

The package is unit tested with 100% line coverage and path coverage. You can
run the tests by simply cloning the source, installing the dependencies, and then
running `./vendor/bin/phpunit`. Additionally included in the developer dependencies
are some Composer scripts which can assist with Code Styling and coverage reporting:

```bash
composer test
composer fix
composer report
```

See the `composer.json` for more details on their execution and reporting output.

## Licensing

Copyright (c) 2018 [Artisans Collaborative](https://artisanscollaborative.com)

This package is released under the MIT license. Please see the LICENSE file
distributed with every copy of the code for commercial licensing terms.
