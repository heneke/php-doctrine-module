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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\ORM\EntityManagerInterface;
use HHIT\Doctrine\Migrations\Contracts\MigrationsConfigurationSource;
use Mockery\MockInterface;

class MigrationsDefaultConfigurationFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MockInterface
     */
    private $configurationSource;

    /**
     * @var MockInterface
     */
    private $entityManager;

    /**
     * @var MockInterface
     */
    private $connection;

    /**
     * @var MigrationsDefaultConfigurationFactory
     */
    private $factory;

    /**
     * @before
     */
    public function before()
    {
        $this->configurationSource = \Mockery::mock(MigrationsConfigurationSource::class);
        $this->configurationSource->shouldReceive('getNamespace')->once()->andReturn('Some\Namespace');
        $this->configurationSource->shouldReceive('getColumnName')->once()->andReturn('column_name');
        $this->configurationSource->shouldReceive('getTableName')->once()->andReturn('table_name');
        $this->configurationSource->shouldReceive('getDirectory')->once()->andReturn('/some/dir');

        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->connection = \Mockery::mock(Connection::class);
        $this->entityManager->shouldReceive('getConnection')->andReturn($this->connection);
        $this->factory = new MigrationsDefaultConfigurationFactory($this->configurationSource, $this->entityManager);
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
    public function create_default()
    {
        $this->configurationSource->shouldReceive('getMode')->once()->andReturnNull();
        $this->configurationSource->shouldReceive('isPlatformDependent')->once()->andReturn(false);

        $configuration = $this->factory->createConfiguration();
        $this->assertFalse($configuration->areMigrationsOrganizedByYear());
        $this->assertFalse($configuration->areMigrationsOrganizedByYearAndMonth());
        $this->assertEquals('Some\Namespace', $configuration->getMigrationsNamespace());
        $this->assertEquals('column_name', $configuration->getMigrationsColumnName());
        $this->assertEquals('table_name', $configuration->getMigrationsTableName());
        $this->assertEquals('/some/dir', $configuration->getMigrationsDirectory());
    }

    /**
     * @test
     */
    public function create_organize_byyear()
    {
        $this->configurationSource->shouldReceive('getMode')->once()->andReturn(MigrationsConfigurationSource::MODE_BYYEAR);
        $this->configurationSource->shouldReceive('isPlatformDependent')->once()->andReturn(false);

        $configuration = $this->factory->createConfiguration();
        $this->assertTrue($configuration->areMigrationsOrganizedByYear());
        $this->assertFalse($configuration->areMigrationsOrganizedByYearAndMonth());
        $this->assertEquals('/some/dir', $configuration->getMigrationsDirectory());
    }

    /**
     * @test
     */
    public function create_organize_byyearandmonth()
    {
        $this->configurationSource->shouldReceive('getMode')->once()->andReturn(MigrationsConfigurationSource::MODE_BYYEARANDMONTH);
        $this->configurationSource->shouldReceive('isPlatformDependent')->once()->andReturn(false);

        $configuration = $this->factory->createConfiguration();
        $this->assertTrue($configuration->areMigrationsOrganizedByYear());
        $this->assertTrue($configuration->areMigrationsOrganizedByYearAndMonth());
        $this->assertEquals('/some/dir', $configuration->getMigrationsDirectory());
    }

    /**
     * @test
     */
    public function create_platform_dependent()
    {
        $this->configurationSource->shouldReceive('getMode')->once()->andReturnNull();
        $this->configurationSource->shouldReceive('isPlatformDependent')->once()->andReturn(true);
        $this->connection->shouldReceive('getDatabasePlatform')->andReturn(new SqlitePlatform());

        $configuration = $this->factory->createConfiguration();
        $this->assertFalse($configuration->areMigrationsOrganizedByYear());
        $this->assertFalse($configuration->areMigrationsOrganizedByYearAndMonth());
        $this->assertEquals('/some/dir/sqlite', $configuration->getMigrationsDirectory());
    }
}
