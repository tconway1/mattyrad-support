<?php namespace MattyRad\Support\Test\Unit;

use MattyRad\Support\Conformation;

class Sample
{
    use Conformation;

    private $array;
    private $str;
    private $int;
    private $bool;
    private $float;
    private $optional1;
    private $optional2;

    private function __construct(
        array $array,
        string $str,
        int $int,
        bool $bool,
        ?float $float,
        $optional1 = '',
        \stdClass $optional2 = null
    ) {
        $this->array = $array;
        $this->str = $str;
        $this->int = $int;
        $this->bool = $bool;
        $this->float = $float;
        $this->optional1 = $optional1;
        $this->optional2 = $optional2;
    }

    public function toArray()
    {
        return [
            'array' => $this->array,
            'str' => $this->str,
            'int' => $this->int,
            'bool' => $this->bool,
            'float' => $this->float,
            'optional1' => $this->optional1,
            'optional2' => $this->optional2,
        ];
    }
}