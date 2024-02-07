<?php namespace MattyRad\Support\Test\Unit\Result;

abstract class BaseTest extends \PHPUnit\Framework\TestCase
{
    protected $result;

    abstract public function test_isSuccess();
    abstract public function test_isFailure();
}