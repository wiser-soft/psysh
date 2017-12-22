<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2017 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\Test\Readline;

use Psy\Readline\Transient;

class TransientTest extends \PHPUnit\Framework\TestCase
{
    public function testHistory()
    {
        $readline = new Transient();
        $this->assertEmpty($readline->listHistory());
        $readline->addHistory('foo');
        $this->assertEquals(['foo'], $readline->listHistory());
        $readline->addHistory('bar');
        $this->assertEquals(['foo', 'bar'], $readline->listHistory());
        $readline->addHistory('baz');
        $this->assertEquals(['foo', 'bar', 'baz'], $readline->listHistory());
        $readline->clearHistory();
        $this->assertEmpty($readline->listHistory());
    }

    /**
     * @depends testHistory
     */
    public function testHistorySize()
    {
        $readline = new Transient(null, 2);
        $this->assertEmpty($readline->listHistory());
        $readline->addHistory('foo');
        $readline->addHistory('bar');
        $this->assertEquals(['foo', 'bar'], $readline->listHistory());
        $readline->addHistory('baz');
        $this->assertEquals(['bar', 'baz'], $readline->listHistory());
        $readline->addHistory('w00t');
        $this->assertEquals(['baz', 'w00t'], $readline->listHistory());
        $readline->clearHistory();
        $this->assertEmpty($readline->listHistory());
    }

    /**
     * @depends testHistory
     */
    public function testHistoryEraseDups()
    {
        $readline = new Transient(null, 0, true);
        $this->assertEmpty($readline->listHistory());
        $readline->addHistory('foo');
        $readline->addHistory('bar');
        $readline->addHistory('foo');
        $this->assertEquals(['bar', 'foo'], $readline->listHistory());
        $readline->addHistory('baz');
        $readline->addHistory('w00t');
        $readline->addHistory('baz');
        $this->assertEquals(['bar', 'foo', 'w00t', 'baz'], $readline->listHistory());
        $readline->clearHistory();
        $this->assertEmpty($readline->listHistory());
    }

    public function testSomeThingsAreAlwaysTrue()
    {
        $readline = new Transient();
        $this->assertTrue(Transient::isSupported());
        $this->assertTrue($readline->readHistory());
        $this->assertTrue($readline->writeHistory());
    }
}
