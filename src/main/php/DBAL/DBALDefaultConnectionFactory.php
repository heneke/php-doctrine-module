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

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\SQLLogger;
use HHIT\Doctrine\DBAL\Contracts\DBALConfigurationFactory;
use HHIT\Doctrine\DBAL\Contracts\DBALConnectionFactory;

class DBALDefaultConnectionFactory implements DBALConnectionFactory
{
    /**
     * @var DBALConfigurationFactory
     */
    private $factory;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var SQLLogger
     */
    private $sqlLogger;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(DBALConfigurationFactory $factory, Cache $cache = null, SQLLogger $sqlLogger = null, EventManager $eventManager = null)
    {
        $this->factory = $factory;
        $this->cache = $cache;
        $this->sqlLogger = $sqlLogger;
        $this->eventManager = $eventManager;
    }

    public function createConnection()
    {
        if ($this->connection === null) {
            $this->connection = $this->createConnectionInternal();
        }

        return $this->connection;
    }

    protected function createConnectionInternal()
    {
        return DriverManager::getConnection($this->factory->getConnectionParameters(), $this->factory->createConfiguration($this->cache, $this->sqlLogger), $this->eventManager);
    }
}
