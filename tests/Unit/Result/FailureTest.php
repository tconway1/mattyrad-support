<?php namespace MattyRad\Support\Test\Unit\Result;

abstract class FailureTest extends BaseTest
{
    protected $result;

    abstract public function test_getContext();
    abstract public function test_toExceptionMessage();

    final public function test_isSuccess()
    {
        $expected = false;
        $actual = $this->result->isSuccess();

        $this->assertEquals($expected, $actual);
    }

    final public function test_isFailure()
    {
        $expected = true;
        $actual = $this->result->isFailure();

        $this->assertEquals($expected, $actual);
    }

    public function test_base_toException()
    {
        $e = $this->result->toException();

        $this->assertInstanceOf(\Exception::class, $e);
    }

    public function test_accessorsThrowException()
    {
        try {
            $this->result->getImportantData();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
            return;
        }

        $this->fail('An exception was not thrown!');
    }
}
