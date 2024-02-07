<?php namespace MattyRad\Support\Test\Unit;

use MattyRad\Support\Conformation;

class Sub1
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

class Sub2
{
    use Conformation;

    private $sub1;
    private $e;
    private $f;

    public function __construct(Sub1 $sub1, string $d, int $e)
    {
        $this->sub1 = $sub1;
        $this->d = $d;
        $this->e = $e;
    }

    public function toArray()
    {
        return get_class_vars($this);
    }

    public function getSub1()
    {
        return $this->sub1;
    }
}


class RecursiveSample
{
    use Conformation;

    private $string;
    private $sub2;

    private function __construct(
        string $string,
        Sub2 $sub2
    ) {
        $this->string = $string;
        $this->sub2 = $sub2;
    }

    public function getSub2()
    {
        return $this->sub2;
    }

    public function toArray()
    {
        return get_class_vars($this);
    }
}
