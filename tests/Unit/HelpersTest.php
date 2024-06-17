<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_ordinal()
    {
        $this->assertEquals('st', ordinal(1));
        $this->assertEquals('nd', ordinal(2));
        $this->assertEquals('rd', ordinal(3));
    }
}
