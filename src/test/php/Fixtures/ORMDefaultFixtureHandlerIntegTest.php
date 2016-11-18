<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Hendrik Heneke
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace HHIT\Doctrine\Fixtures;

use HHIT\Doctrine\AbstractIntegTest;

class ORMDefaultFixtureHandlerIntegTest extends AbstractIntegTest
{
    /**
     * @var ORMDefaultFixtureHandler
     */
    private $handler;

    /**
     * @before
     */
    public function before()
    {
        parent::before();
        $this->migrationsHandler->migrateToLatest();
        $this->handler = new ORMDefaultFixtureHandler($this->entityManager);
    }

    /**
     * @test
     */
    public function loadFromFile()
    {
        $this->assertEquals(0, $this->recordCount('sample'));
        $this->handler->load(__DIR__ . '/../../fixtures/SampleFixtures.php', false);
        $this->assertEquals(1, $this->recordCount('sample'));
    }

    /**
     * @test
     */
    public function loadWithPurge()
    {
        $this->assertEquals(0, $this->recordCount('sample'));
        $this->handler->load(__DIR__ . '/../../fixtures', false);
        $this->assertEquals(1, $this->recordCount('sample'));
    }

    /**
     * @test
     */
    public function loadWithAppend()
    {
        $this->assertEquals(0, $this->recordCount('sample'));
        $this->handler->load(__DIR__ . '/../../fixtures', false);
        $this->assertEquals(1, $this->recordCount('sample'));
        $this->handler->load(__DIR__ . '/../../fixtures', true);
        $this->assertEquals(2, $this->recordCount('sample'));
    }

    /**
     * @test
     */
    public function purgeWithDelete()
    {
        $this->assertEquals(0, $this->recordCount('sample'));
        $this->handler->load(__DIR__ . '/../../fixtures', true);
        $this->assertEquals(1, $this->recordCount('sample'));
        $this->handler->purge(false);
        $this->assertEquals(0, $this->recordCount('sample'));
    }

    /**
     * @test
     */
    public function purgeWithTruncate()
    {
        $this->assertEquals(0, $this->recordCount('sample'));
        $this->handler->load(__DIR__ . '/../../fixtures', true);
        $this->assertEquals(1, $this->recordCount('sample'));
        $this->handler->purge(true);
        $this->assertEquals(0, $this->recordCount('sample'));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedException Fixtures required
     */
    public function nullFixtures()
    {
        $this->handler->load(null);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedException Could not find any fixtures
     */
    public function emptyFixtures()
    {
        $this->handler->load(__DIR__ . '/../../empty_fixtures');
    }
}
