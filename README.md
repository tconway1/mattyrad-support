# MattyRad Support

![Build Status](https://api.travis-ci.org/MattyRad/support.png?branch=master) ![Code Coverage](https://img.shields.io/codecov/c/github/mattyrad/support.svg)

## Installation

`composer require mattyrad/support`

## Table of Contents

- [Conformation Trait](#conformation-trait)
- [Result Objects](#result-objects)

### Conformation Trait
#### Instantiate objects from an unsorted array

```php
use MattyRad\Support\Conformation;

class Sample {
    use Conformation;

    private $arg1;
    private $arg2;
    private $arg3;
    private $arg4;
    private $optional1;
    private $optional2;

    private function __construct(
        string $arg1,
        int $arg2,
        bool $arg3,
        float $arg4,
        string $optional1 = '',
        int $optional2 = 1
    ) {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
        $this->arg3 = $arg3;
        $this->arg4 = $arg4;
        $this->optional1 = $optional1;
        $this->optional2 = $optional2;
    }
}
```

```php
$sample = Sample::fromArray([
    'optional2' => 777,
    'arg2' => 1,
    'arg3' => false,
    'arg1' => 'example',
    'arg4' => 2.0,
]);
```

Failing to provide all the required arguments will throw an Exception

```php
$sample = Sample::fromArray([
    'arg1' => 'example',
    'arg2' => 1,
]);
```
`PHP Fatal error:  Uncaught InvalidArgumentException: Sample missing key(s): arg3, arg4`

### Result Objects

It's very common to require extensible result objects for success and failures, particularly for APIs.

#### Defining Results

You can hit the ground running with generic success results

```php
$json_response_data = ['user' => ['name' => 'John', 'email' => 'user@example.com', 'posts' => [['name' => 'A'], ['name' => 'B']]]];

$result = new \MattyRad\Support\Result\Success($json_response_data);

$result->isSuccess(); // true
$result->isFailure(); // false
$result->get('user.email'); // dot syntax enabled
$result->get('user.posts.*.name'); // wildcard enabled, ['A', 'B']
```

For more precision, you can extend the Success result
```php
class WidgetPurchased extends Result\Success
{
    public function __construct(Widget $widget)
    {
        $this->widget = $widget;
    }

    public function getWidget(): Widget
    {
        return $this->widget;
    }
}

$result = new Result\Success\WidgetPurchased($widget);

$result->getWidget(); // Widget object
$result->isSuccess(); // true
$result->isFailure(); // false
```

Failure results are required to be a bit more specific

```php
namespace MattyRad\Support\Result\Stripe;

use MattyRad\Support\Result;

class ChargeFailed extends Result\Failure
{
    protected static $message = 'Stripe charge failed, delinquent card';

    public function __construct($last_four_digits)
    {
        $this->last_four_digits = $last_four_digits;
    }

    public function getContext()
    {
        return ['last_four_digits' => $this->last_four_digits];
    }
}

$result->isSuccess(); // false
$result->isFailure(); // true
$result->get('widget.name'); // throws exception with message 'Stripe charge failed, delinquent card'
$result->getWidget(); // also throws exception with message 'Stripe charge failed, delinquent card'
$result->getMessage(); // 'Stripe charge failed, delinquent card'
$result->getContext(); // ['last_four_digits' => '1234']
$result->getReason(); // 'Stripe charge failed, delinquent card; {"last_four_digits":"1234"}'
```

#### Instantiating and Returning Results

```php
use MattyRad\Support\Result;

function purchaseWidget($user, string $widget_name): Result
{
    if ($existing_widget = $this->db->getWidgetByName($widget_name)) {
        return new Result\Failure\WidgetExists($existing_widget);
    }

    try {
        $user->charge(100); // API call, this could be any interface to stripe
    } catch (\Stripe\Error\Card $e) {
        return new Result\Failure\Stripe\ChargeFailed($e->getLastFour()); // pretend that getLastFour exists
    }

    $widget = new Widget($widget_name);

    return new Result\Success\WidgetPurchased($widget);
}
```

#### Consuming Results

You can check for a failure manually

```php
$result = purchaseWidget(Auth::user(), 'my_cool_new_widget');

if ($result->isFailure()) {
    return new JsonResponse(['error' => $result->getReason()], 422);
}

return new JsonResponse($result->getWidget());
```

Alternatively, you can leverage Exceptions and cut out the success/failure checks. Attempting to access data from a Failure Result will cause it to throw an Exception

```php
$result = purchaseWidget(Auth::user(), 'my_cool_new_widget'); // returns ChargeFailed

return new Response($result->getWidget()); // Throws exception
```

Laravel's Exception Handler handles a number of exceptions by default (like HttpResponseException), which we can use to our advantage by overriding the default toException function of the Failure Result

```php
class ChargeFailed extends Result\Failure
{
    // ...

    public function toException()
    {
        $response = new JsonResponse(['error' => static::$message], 422);

        return new HttpResponseException($response);
    }
```

And error handling will be built in, we can focus on the success path. Any non-built in exceptions can get caught in the render() function of the Exception Handler.
