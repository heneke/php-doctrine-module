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

namespace HHIT\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use HHIT\Doctrine\DBAL\Contracts\DBALConfigurationFactory;
use HHIT\Doctrine\DBAL\Contracts\DBALConfigurationSource;
use HHIT\Doctrine\DBAL\Contracts\DBALConnectionFactory;
use HHIT\Doctrine\DBAL\DBALDefaultConfigurationFactory;
use HHIT\Doctrine\DBAL\DBALDefaultConnectionFactory;
use HHIT\Doctrine\DBAL\DBALFileConfigurationSource;
use HHIT\Doctrine\Migrations\Contracts\MigrationsConfigurationFactory;
use HHIT\Doctrine\Migrations\Contracts\MigrationsConfigurationSource;
use HHIT\Doctrine\Migrations\Contracts\MigrationsHandler;
use HHIT\Doctrine\Migrations\MigrationsDefaultConfigurationFactory;
use HHIT\Doctrine\Migrations\MigrationsDefaultHandler;
use HHIT\Doctrine\Migrations\MigrationsFileConfigurationSource;
use HHIT\Doctrine\ORM\Contracts\EntityManagerConfigurationFactory;
use HHIT\Doctrine\ORM\Contracts\EntityManagerConfigurationSource;
use HHIT\Doctrine\ORM\Contracts\EntityManagerFactory;
use HHIT\Doctrine\ORM\EntityManagerDefaultConfigurationFactory;
use HHIT\Doctrine\ORM\EntityManagerDefaultFactory;
use HHIT\Doctrine\ORM\EntityManagerFileConfigurationSource;

abstract class AbstractIntegTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DBALConfigurationSource
     */
    private $dbalConfigurationSource;

    /**
     * @var DBALConfigurationFactory
     */
    private $dbalConfigurationFactory;

    /**
     * @var DBALConnectionFactory
     */
    private $dbalConnectionFactory;

    /**
     * @var EntityManagerConfigurationSource
     */
    private $entityManagerConfigurationSource;

    /**
     * @var EntityManagerConfigurationFactory
     */
    private $entityManagerConfigurationFactory;

    /**
     * @var EntityManagerFactory
     */
    private $entityManagerFactory;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var MigrationsConfigurationSource
     */
    private $migrationsConfigurationSource;

    /**
     * @var MigrationsConfigurationFactory
     */
    private $migrationsConfigurationFactory;

    /**
     * @var Configuration
     */
    protected $migrationsConfiguration;

    /**
     * @var MigrationsHandler
     */
    protected $migrationsHandler;

    /**
     * @before
     */
    public function before()
    {
        $this->dbalConfigurationSource = new DBALFileConfigurationSource(__DIR__.'/../configs/doctrine.config.php', 'dbal');
        $this->dbalConfigurationFactory = new DBALDefaultConfigurationFactory($this->dbalConfigurationSource);
        $this->dbalConnectionFactory = new DBALDefaultConnectionFactory($this->dbalConfigurationFactory);

        $this->entityManagerConfigurationSource = new EntityManagerFileConfigurationSource(__DIR__.'/../configs/doctrine.config.php', 'orm');
        $this->entityManagerConfigurationFactory = new EntityManagerDefaultConfigurationFactory($this->entityManagerConfigurationSource);
        $this->entityManagerFactory = new EntityManagerDefaultFactory($this->entityManagerConfigurationFactory, $this->dbalConnectionFactory);

        $this->entityManager = $this->entityManagerFactory->createEntityManager();
        $this->connection = $this->entityManager->getConnection();

        $this->migrationsConfigurationSource = new MigrationsFileConfigurationSource(__DIR__.'/../configs/doctrine.config.php', 'migrations');
        $this->migrationsConfigurationFactory = new MigrationsDefaultConfigurationFactory($this->migrationsConfigurationSource, $this->entityManager);
        $this->migrationsConfiguration = $this->migrationsConfigurationFactory->createConfiguration();

        $this->migrationsHandler = new MigrationsDefaultHandler($this->migrationsConfiguration);
    }

    protected function tableExists($name)
    {
        return $this->connection->getSchemaManager()->tablesExist([$name]);
    }

    protected function recordCount($table)
    {
        return $this->connection->executeQuery("SELECT COUNT(*) FROM {$table}")->fetchColumn(0);
    }
}
