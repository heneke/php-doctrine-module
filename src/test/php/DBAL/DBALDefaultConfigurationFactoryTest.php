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

namespace HHIT\Doctrine\DBAL;

use Doctrine\Common\Cache\Cache;
use Doctrine\DBAL\Logging\SQLLogger;
use HHIT\Doctrine\DBAL\Contracts\DBALConfigurationSource;
use Mockery\MockInterface;

class DBALDefaultConfigurationFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MockInterface
     */
    private $source;

    /**
     * @var DBALDefaultConfigurationFactory
     */
    private $factory;

    /**
     * @before
     */
    public function before()
    {
        $this->source = \Mockery::mock(DBALConfigurationSource::class);
        $this->factory = new DBALDefaultConfigurationFactory($this->source);
    }

    private function configureMock($cache = false, $log = false, $filter = null, $url = 'sqlite:///:memory:', $autoCommit = false)
    {
        $this->source
            ->shouldReceive('getConnnectionParameters')->andReturn(['url' => $url])
            ->shouldReceive('isAutoCommit')->andReturn($autoCommit)
            ->shouldReceive('isCacheResults')->andReturn($cache)
            ->shouldReceive('isLogQueries')->andReturn($log)
            ->shouldReceive('getFilterSchemaAssetsExpression')->andReturn($filter);
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Caching is active
     */
    public function no_cache()
    {
        $this->configureMock(true);
        $this->factory->createConfiguration();
    }

    /**
     * @test
     */
    public function cache()
    {
        $this->configureMock(true);
        $cache = \Mockery::mock(Cache::class);
        $config = $this->factory->createConfiguration($cache, null);
        $this->assertNotNull($config->getResultCacheImpl());
        $this->assertNull($config->getSQLLogger());
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Logging is active
     */
    public function no_log()
    {
        $this->configureMock(false, true);
        $this->factory->createConfiguration();
    }

    /**
     * @test
     */
    public function log()
    {
        $this->configureMock(false, true);
        $log = \Mockery::mock(SQLLogger::class);
        $config = $this->factory->createConfiguration(null, $log);
        $this->assertNull($config->getResultCacheImpl());
        $this->assertNotNull($config->getSQLLogger());
    }
}
