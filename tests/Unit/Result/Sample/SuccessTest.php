<?php namespace MattyRad\Support\Test\Unit\Result\Sample;

use MattyRad\Support\Test;
use MattyRad\Support\Result;

class SuccessTest extends Test\Unit\Result\SuccessTest
{
    const RESPONSE_DATA = ['k' => 'v'];

    protected $result;

    public function setUp()
    {
        $this->result = new class(self::RESPONSE_DATA) extends Result\Success {};
    }

    public function tearDown()
    {
        unset($this->result);
    }

    public function test_get()
    {
        $this->assertEquals('v', $this->result->get('k'));
        $this->assertEquals(null, $this->result->get('dne'));
    }

    public function test_toArray()
    {
        $actual = $this->result->toArray();

        $this->assertEquals(self::RESPONSE_DATA, $actual);
    }

    public function test_toJson()
    {
        $actual = json_encode($this->result);

        $this->assertEquals(json_encode(self::RESPONSE_DATA), $actual);
    }
}