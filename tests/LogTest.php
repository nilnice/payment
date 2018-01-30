<?php

namespace Nilnice\Payment\Test;

use Nilnice\Payment\Log;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function testConvertPSR3ToMonologLevel()
    {
        self::assertEquals(Log::toMonologLevel('info'), 200);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetLevelNameThrows()
    {
        Log::getLevelName(700);
    }
}
