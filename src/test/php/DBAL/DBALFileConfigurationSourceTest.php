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

namespace HHIT\Doctrine\DBAL;

use PHPUnit\Framework\TestCase;

class DBALFileConfigurationSourceTest extends TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage File required
     */
    public function no_file()
    {
        new DBALFileConfigurationSource(null);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage does not exist
     */
    public function nonexisting_file()
    {
        new DBALFileConfigurationSource(__FILE__.'.does.not.exist');
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    public function not_array()
    {
        new DBALFileConfigurationSource(__DIR__.'/../../configs/invalid.config.php');
    }

    /**
     * @test
     */
    public function full()
    {
        $source = new DBALFileConfigurationSource(__DIR__.'/../../configs/dbal.full.config.php');
        $this->assertEquals('sqlite:///:memory:', $source->getConnectionParameters()['url']);
        $this->assertTrue($source->isAutoCommit());
        $this->assertTrue($source->isLogQueries());
        $this->assertTrue($source->isCacheResults());
        $this->assertEquals('/^schema.*$/', $source->getFilterSchemaAssetsExpression());
    }

    /**
     * @test
     */
    public function minimal()
    {
        $source = new DBALFileConfigurationSource(__DIR__.'/../../configs/dbal.minimal.config.php');
        $this->assertEquals('sqlite:///:memory:', $source->getConnectionParameters()['url']);
        $this->assertFalse($source->isAutoCommit());
        $this->assertFalse($source->isCacheResults());
        $this->assertFalse($source->isLogQueries());
        $this->assertNull($source->getFilterSchemaAssetsExpression());
    }
}
