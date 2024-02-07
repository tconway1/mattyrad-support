<?php namespace MattyRad\Support\Test\Unit;

use MattyRad\Support\Conformation;

class SubConformation
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

class Sample2
{
    use Conformation;

    private $string;
    private $sub;

    private function __construct(
        string $string,
        SubConformation $sub
    ) {
        $this->string = $string;
        $this->sub = $sub;
    }

    public function getSub()
    {
        return $this->sub;
    }

    public function toArray()
    {
        return get_class_vars($this);
    }
}
