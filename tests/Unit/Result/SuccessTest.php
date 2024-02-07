<?php namespace MattyRad\Support\Test\Unit\Result;

use MattyRad\Support\Result;

abstract class SuccessTest extends BaseTest
{
    protected $result;

    final public function test_isSuccess()
    {
        $expected = true;
        $actual = $this->result->isSuccess();

        $this->assertEquals($expected, $actual);
    }

    final public function test_isFailure()
    {
        $expected = false;
        $actual = $this->result->isFailure();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider responseDataProvider
     */
    public function test_get_isDotWildcardSyntaxEnabled(array $response_data, $key, $expected)
    {
        $r = new class($response_data) extends Result\Success {};

        $actual = $r->get($key);

        $this->assertEquals($expected, $actual);
    }

    public function responseDataProvider()
    {
        return [
            'empty' => [
                [],
                'c.z',
                null,
            ],
            'can get ordinary array values' => [
                ['a' => 1, 'b' => 2, 'c' => ['z' => 3]],
                'a',
                1,
            ],
            'is dot syntax enabled' => [
                ['a' => 1, 'b' => 2, 'c' => ['z' => 3]],
                'c.z',
                3,
            ],
            'is wildcard enabled' => [
                ['a' => [['b' => 1], ['b' => 2]]],
                'a.*.b',
                [1, 2],
            ],
            'is wildcard enabled (multiple wildcards)' => [
                ['a' => [['b' => [['c' => 'what'], ['c' => 'is']]], ['b' => [['c' => 'up'], ['c' => 'dawg']]]]],
                'a.*.b.*.c',
                ['what', 'is', 'up', 'dawg'],
            ],
            'is wildcard enabled (wildcard terminated)' => [
                ['a' => ['some', 'random', 'values']],
                'a.*',
                ['some', 'random', 'values'],
            ],
            'is chained wildcard enabled' => [
                ['a' => [['some'], ['other'], ['values']]],
                'a.*.*',
                ['some', 'other', 'values'],
            ],
        ];
    }
}