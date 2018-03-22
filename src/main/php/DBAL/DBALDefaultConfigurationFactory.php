<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2018 Hendrik Heneke
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
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Logging\SQLLogger;
use HHIT\Doctrine\DBAL\Contracts\DBALConfigurationFactory;
use HHIT\Doctrine\DBAL\Contracts\DBALConfigurationSource;

class DBALDefaultConfigurationFactory implements DBALConfigurationFactory
{
    /**
     * @var DBALConfigurationSource
     */
    private $configurationSource;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(DBALConfigurationSource $configurationSource)
    {
        $this->configurationSource = $configurationSource;
    }

    public function createConfiguration(Cache $cache = null, SQLLogger $logger = null)
    {
        if ($this->configuration === null) {
            $this->configuration = $this->createConfigurationInternal($cache, $logger);
        }

        return $this->configuration;
    }

    protected function createConfigurationInternal(Cache $cache = null, SQLLogger $logger = null)
    {
        $configuration = new Configuration();
        $configuration->setAutoCommit($this->configurationSource->isAutoCommit());
        $configuration->setFilterSchemaAssetsExpression($this->configurationSource->getFilterSchemaAssetsExpression());
        if ($this->configurationSource->isCacheResults()) {
            if ($cache === null) {
                throw new \UnexpectedValueException('Caching is active, but no cache configured!');
            }
            $configuration->setResultCacheImpl($cache);
        }
        if ($this->configurationSource->isLogQueries()) {
            if ($logger === null) {
                throw new \UnexpectedValueException('Logging is active, but no logger configured!');
            }
            $configuration->setSQLLogger($logger);
        }

        return $configuration;
    }

    public function getConnectionParameters()
    {
        return $this->configurationSource->getConnectionParameters();
    }
}
