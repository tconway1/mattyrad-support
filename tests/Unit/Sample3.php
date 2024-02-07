<?php namespace MattyRad\Support\Test\Unit;

use MattyRad\Support\Conformation;

class SubConformation2
{
    use Conformation;

    private $a;
    private $b;
    private $c;

    public function __construct(string $a, int $b, string $c = '')
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }

    public function toArray()
    {
        return get_class_vars($this);
    }
}

class Sample3
{
    use Conformation;

    private static $array_conformations = [
        'subconformations' => SubConformation2::class,
    ];

    private $string;
    private $subconformations;

    private function __construct(
        string $string,
        array $subconformations
    ) {
        $this->string = $string;
        $this->subconformations = $subconformations;
    }

    public function getSubconformations(): array
    {
        return $this->subconformations;
    }

    public function toArray()
    {
        return get_class_vars($this);
    }
}
