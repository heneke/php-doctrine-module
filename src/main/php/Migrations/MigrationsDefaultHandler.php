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

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\MigratorConfiguration;
use HHIT\Doctrine\Migrations\Contracts\MigrationsHandler;

class MigrationsDefaultHandler implements MigrationsHandler
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var DependencyFactory
     */
    private $dependencyFactory;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->dependencyFactory = new DependencyFactory($this->configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableVersions()
    {
        return $this->configuration->getAvailableVersions();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentVersion()
    {
        return $this->configuration->getCurrentVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function getLatestVersion()
    {
        return $this->configuration->getLatestVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function migrateTo($to, $rebuild = false, $dryRun = false)
    {
        $migratorConfiguration = new MigratorConfiguration();
        $migratorConfiguration->setAllOrNothing(true);
        $migratorConfiguration->setDryRun($dryRun);
        $migratorConfiguration->setTimeAllQueries(false);

        if ($rebuild) {
            $this->dependencyFactory->getMigrator()->migrate(0, $migratorConfiguration);
        }
        $this->dependencyFactory->getMigrator()->migrate($to, $migratorConfiguration);
    }

    /**
     * {@inheritdoc}
     */
    public function migrateToLatest($rebuild = false, $dryRun = false)
    {
        $this->migrateTo($this->getLatestVersion(), $rebuild, $dryRun);
    }
}
