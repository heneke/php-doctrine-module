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

namespace HHIT\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use HHIT\Doctrine\Migrations\Contracts\MigrationsConfigurationFactory;
use HHIT\Doctrine\Migrations\Contracts\MigrationsConfigurationSource;

class MigrationsDefaultConfigurationFactory implements MigrationsConfigurationFactory
{
    /**
     * @var MigrationsConfigurationSource
     */
    private $source;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(MigrationsConfigurationSource $source, EntityManagerInterface $entityManager)
    {
        $this->source = $source;
        $this->entityManager = $entityManager;
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
        switch ($this->source->getMode()) {
            case MigrationsConfigurationSource::MODE_BYYEAR:
                $configuration->setMigrationsAreOrganizedByYear(true);
                break;
            case MigrationsConfigurationSource::MODE_BYYEARANDMONTH:
                $configuration->setMigrationsAreOrganizedByYearAndMonth(true);
                break;
            default:
                break;
        }
        $configuration->setMigrationsNamespace($this->source->getNamespace());
        $configuration->setMigrationsColumnName($this->source->getColumnName());
        $configuration->setMigrationsTableName($this->source->getTableName());
        $directory = $this->source->getDirectory();
        if ($this->source->isPlatformDependent()) {
            $directory .= DIRECTORY_SEPARATOR.$this->entityManager->getConnection()->getDatabasePlatform()->getName();
        }
        $configuration->setMigrationsDirectory($directory);

        return $configuration;
    }
}
