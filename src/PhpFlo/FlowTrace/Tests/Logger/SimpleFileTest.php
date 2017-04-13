<?php
/*
 * This file is part of the phpflo/flowtrace package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpFlo\Logger\Tests;

use org\bovigo\vfs\vfsStream;
use PhpFlo\FlowTrace\Test\TestCase;
use PhpFlo\Logger\SimpleFile;
use Psr\Log\LogLevel;

class SimpleFileTest extends TestCase
{
    public function testBaseFunctionality()
    {
        $expected = <<< EOF
first_line
second_line
third_line
fourth_line
fifth_line
sixth_line
seventh_line
eighth_line
last_line

EOF;

        vfsStream::setup('home');
        $file = vfsStream::url('home/test.log');

        $logger = new SimpleFile($file, 'debug');
        $logger->debug('first_line');
        $logger->alert('second_line');
        $logger->critical('third_line');
        $logger->emergency('fourth_line');
        $logger->error('fifth_line');
        $logger->info('sixth_line');
        $logger->notice('seventh_line');
        $logger->warning('eighth_line');
        $logger->log(LogLevel::DEBUG, 'last_line');

        $this->assertEquals($expected, file_get_contents($file));

        $logger = null;
    }

    public function testDefaultFile()
    {
        $dir = vfsStream::setup('home');
        $logger = new SimpleFile($dir->url(), 'debug');
        $logger->log(LogLevel::DEBUG, 'last_line');
        $this->assertTrue($dir->hasChild('home' . DIRECTORY_SEPARATOR .  'flow.log'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNoDirFound()
    {
        $logger = new SimpleFile('i_do_not_exist/because/i_am_not_here');
    }
}
