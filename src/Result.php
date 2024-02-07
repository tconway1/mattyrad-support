<?php namespace MattyRad\Support;

interface Result extends \JsonSerializable
{
    public function isSuccess();
    public function isFailure();
    public function toArray(): array;
}