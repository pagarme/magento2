<?php

namespace Pagarme\Pagarme\Test\Unit;

use PHPUnit\Framework\TestCase;
use Mockery;

abstract class BaseTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }
}
