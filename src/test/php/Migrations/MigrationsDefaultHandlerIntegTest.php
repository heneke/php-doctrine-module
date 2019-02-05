<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2019 Hendrik Heneke
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

namespace HHIT\Doctrine\Migrations;

use HHIT\Doctrine\AbstractIntegTest;

class MigrationsDefaultHandlerIntegTest extends AbstractIntegTest
{
    /**
     * @var MigrationsDefaultHandler
     */
    private $handler;

    /**
     * @before
     */
    public function before()
    {
        parent::before();
        $this->handler = new MigrationsDefaultHandler($this->migrationsConfiguration);
    }

    /**
     * @test
     */
    public function migrateToLatest()
    {
        $this->assertFalse($this->tableExists('sample'));
        $this->handler->migrateToLatest();
        $this->assertTrue($this->tableExists('sample'));
    }

    /**
     * @test
     */
    public function migrateToLatestWithRebuild()
    {
        $this->assertFalse($this->tableExists('sample'));
        $this->handler->migrateToLatest();
        $this->assertTrue($this->handler->getCurrentVersion() != '0');
        $this->assertTrue($this->tableExists('sample'));
        $this->connection->insert('sample', ['value' => 'value']);
        $this->assertEquals(1, $this->recordCount('sample'));
        $this->handler->migrateToLatest(true);
        $this->assertEquals(0, $this->recordCount('sample'));
    }

    /**
     * @test
     */
    public function migrateToLatestDryRun()
    {
        $this->assertFalse($this->tableExists('sample'));
        $this->handler->migrateToLatest(false, true);
        $this->assertFalse($this->tableExists('sample'));
    }

    /**
     * @test
     */
    public function availableVersions()
    {
        $this->assertCount(1, $this->handler->getAvailableVersions());
    }

    /**
     * @test
     */
    public function currentVersion()
    {
        $this->assertEquals('0', $this->handler->getCurrentVersion());
    }

    /**
     * @test
     */
    public function latestVersion()
    {
        $this->assertTrue($this->handler->getLatestVersion() != '0');
    }
}
