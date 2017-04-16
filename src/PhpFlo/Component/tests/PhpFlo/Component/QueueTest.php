<?php

namespace Tests\PhpFlo\Component;

use PhpFlo\Network;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    public function testNoMessageIsForwardedWhileQueueSizeIsNotReached()
    {
        $this->markTestIncomplete();
    }

    public function testAllMessagesAreForwardedWhenQueueSizeIsReached()
    {
        $this->markTestIncomplete();
    }

    public function testQueueResize()
    {
        $this->markTestIncomplete();
    }

    public function testErrorIsSendWhenIncorrectResizeMessageIsReceived()
    {
        $this->markTestIncomplete();
    }

    public function testMessagesAreForwardeWhenQueueIsResizedBelowCurrentMessageCount()
    {
        $this->markTestIncomplete();
    }

    public function testMessagesAreForwaredWhenIncomingStreamIsDetached()
    {
        $this->markTestIncomplete();
    }
}
