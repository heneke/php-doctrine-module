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

use Doctrine\DBAL\Configuration;
use HHIT\Doctrine\DBAL\Contracts\DBALConfigurationFactory;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class DBALDefaultConnectionFactoryTest extends TestCase
{
    /**
     * @var MockInterface
     */
    private $source;

    /**
     * @var DBALDefaultConnectionFactory
     */
    private $factory;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @before
     */
    public function before()
    {
        $this->source = \Mockery::mock(DBALConfigurationFactory::class);
        $this->source
            ->shouldReceive('getConnectionParameters')->atLeast()->once()->andReturn(['url' => 'sqlite:///:memory:'])
            ->shouldReceive('createConfiguration')->atLeast()->once()->andReturn($this->configuration);

        $this->factory = new DBALDefaultConnectionFactory($this->source);
        $this->configuration = new Configuration();
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
     */
    public function create()
    {
        $connection = $this->factory->createConnection();
        $this->assertNotNull($connection);
        $this->assertTrue($connection->isAutoCommit());
    }
}
