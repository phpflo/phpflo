<?php

namespace PhpFlo\Tests\Component;

use PhpFlo\Network;

class QueuTest extends \PHPUnit_Framework_TestCase
{
    public function testNoMessageIsForwardedWhileQueuSizeIsNotReached()
    {
        $this->markTestIncomplete();
    }

    public function testAllMessagesAreForwardedWhenQueuSizeIsReached()
    {
        $this->markTestIncomplete();
    }

    public function testQueuResize()
    {
        $this->markTestIncomplete();
    }

    public function testErrorIsSendWhenIncorrectResizeMessageIsReceived()
    {
        $this->markTestIncomplete();
    }

    public function testMessagesAreForwardeWhenQueuIsResizedBelowCurrentMessageCount()
    {
        $this->markTestIncomplete();
    }

    public function testMessagesAreForwaredWhenIncomingStreamIsDetached()
    {
        $this->markTestIncomplete();
    }
}
