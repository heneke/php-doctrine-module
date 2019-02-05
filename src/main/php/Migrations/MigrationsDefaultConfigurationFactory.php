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
use Doctrine\ORM\EntityManagerInterface;
use HHIT\Doctrine\Migrations\Contracts\MigrationsConfigurationFactory;
use HHIT\Doctrine\Migrations\Contracts\MigrationsConfigurationSource;
use HHIT\Doctrine\ORM\Contracts\EntityManagerConfigurationSource;
use HHIT\Doctrine\ORM\Contracts\PersistenceUnit;

class MigrationsDefaultConfigurationFactory implements MigrationsConfigurationFactory
{
    /**
     * @var MigrationsConfigurationSource
     */
    private $migrationsConfigurationSource;

    /**
     * @var EntityManagerConfigurationSource
     */
    private $entityManagerConfigurationSource;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(MigrationsConfigurationSource $migrationConfigurationSource, EntityManagerConfigurationSource $entityManagerConfigurationSource, EntityManagerInterface $entityManager)
    {
        $this->migrationsConfigurationSource = $migrationConfigurationSource;
        $this->entityManagerConfigurationSource = $entityManagerConfigurationSource;
        $this->entityManager = $entityManager;
        $this->entityManagerConfigurationSource = $entityManagerConfigurationSource;
    }

    public function createConfiguration()
    {
        if ($this->configuration === null) {
            $this->configuration = $this->createConfigurationInternal();
        }

        return $this->configuration;
    }

    /**
     * @return Configuration
     */
    protected function createConfigurationInternal()
    {
        $configuration = new Configuration($this->entityManager->getConnection());
        switch ($this->migrationsConfigurationSource->getMode()) {
            case MigrationsConfigurationSource::MODE_BYYEAR:
                $configuration->setMigrationsAreOrganizedByYear(true);
                break;
            case MigrationsConfigurationSource::MODE_BYYEARANDMONTH:
                $configuration->setMigrationsAreOrganizedByYearAndMonth(true);
                break;
            default:
                break;
        }
        $configuration->setMigrationsNamespace($this->migrationsConfigurationSource->getNamespace());
        $configuration->setMigrationsColumnName($this->migrationsConfigurationSource->getColumnName());
        $configuration->setMigrationsTableName($this->migrationsConfigurationSource->getTableName());
        $directory = $this->migrationsConfigurationSource->getOutputDirectory();
        $platformDependent = $this->migrationsConfigurationSource->isPlatformDependent();
        $platform = '';
        if ($platformDependent) {
            $platform = $this->entityManager->getConnection()->getDatabasePlatform()->getName();
            $directory .= DIRECTORY_SEPARATOR.$platform;
        }
        $configuration->setMigrationsDirectory($directory);

        $allMigrationsDirectories = [];
        foreach ($this->entityManagerConfigurationSource->getPersistenceUnits() as $persistenceUnit) {
            /**
             * @var PersistenceUnit
             */
            $persistenceUnit = new $persistenceUnit();
            $migrationsDirectories = $persistenceUnit->getMigrationsDirectories();
            if ($migrationsDirectories !== null) {
                $migrationsDirectories = array_map(function ($d) use ($platformDependent, $platform) {
                    if ($platformDependent) {
                        return $d.DIRECTORY_SEPARATOR.$platform;
                    } else {
                        return $d;
                    }
                }, (array) $migrationsDirectories);
                $allMigrationsDirectories = array_merge($allMigrationsDirectories, $migrationsDirectories);
            }
        }
        foreach ($allMigrationsDirectories as $migrationsDirectory) {
            $configuration->registerMigrationsFromDirectory($migrationsDirectory);
        }

        return $configuration;
    }
}
