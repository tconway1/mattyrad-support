<?php namespace MattyRad\Support\Test\Unit\Result\Sample;

use MattyRad\Support\Test;
use MattyRad\Support\Result;

class Failure extends Result\Failure {

    protected static $message = 'The thing did not work';

    private $context;

    public function __construct(array $context)
    {
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}

class FailureTest extends Test\Unit\Result\FailureTest
{
    protected $result;

    private $context;

    public function setUp()
    {
        $this->context = ['here', 'is', 'some', 'of', 'the', 'data', 'that', 'caused', 'the', 'failure'];

        $this->result = new Failure($this->context);
    }

    public function tearDown()
    {
        unset(
            $this->result,
            $this->context
        );
    }

    public function test_getContext()
    {
        $actual = $this->result->getContext();

        $this->assertEquals($this->context, $actual);
    }

    public function test_getReason()
    {
        $expected = 'The thing did not work; ' . json_encode($this->result->getContext());
        $actual = $this->result->getReason();

        $this->assertEquals($expected, $actual);
    }

    public function test_toExceptionMessage()
    {
        $e = $this->result->toException();

        $this->assertEquals('The thing did not work', $e->getMessage());
    }
}
