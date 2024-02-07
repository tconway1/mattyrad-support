<?php namespace MattyRad\Support\Result;

use MattyRad\Support\Result;

abstract class Base implements Result
{
    abstract public function isSuccess();
    abstract public function isFailure();
}