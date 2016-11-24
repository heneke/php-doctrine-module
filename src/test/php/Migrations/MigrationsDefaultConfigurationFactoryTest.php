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
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use HHIT\Doctrine\App\ORM\SamplePersistenceUnit;
use HHIT\Doctrine\Migrations\Contracts\MigrationsConfigurationSource;
use HHIT\Doctrine\ORM\Contracts\EntityManagerConfigurationSource;
use Mockery\MockInterface;

class MigrationsDefaultConfigurationFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MockInterface
     */
    private $migrationsConfigurationSource;

    /**
     * @var MockInterface
     */
    private $entityManagerConfigurationSource;

    /**
     * @var MockInterface
     */
    private $entityManager;

    /**
     * @var Connection
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
        $this->migrationsConfigurationSource = \Mockery::mock(MigrationsConfigurationSource::class);
        $this->migrationsConfigurationSource->shouldReceive('getNamespace')->once()->andReturn('Some\Name\Space');
        $this->migrationsConfigurationSource->shouldReceive('getColumnName')->once()->andReturn('column_name');
        $this->migrationsConfigurationSource->shouldReceive('getTableName')->once()->andReturn('table_name');
        $this->migrationsConfigurationSource->shouldReceive('getOutputDirectory')->once()->andReturn('/some/dir');

        $this->entityManagerConfigurationSource = \Mockery::mock(EntityManagerConfigurationSource::class);
        $persistenceUnits = [SamplePersistenceUnit::class];
        $this->entityManagerConfigurationSource->shouldReceive('getPersistenceUnits')->andReturn($persistenceUnits)->once();

        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->connection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $this->entityManager->shouldReceive('getConnection')->andReturn($this->connection);
        $this->factory = new MigrationsDefaultConfigurationFactory($this->migrationsConfigurationSource, $this->entityManagerConfigurationSource, $this->entityManager);
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
        $this->migrationsConfigurationSource->shouldReceive('getMode')->once()->andReturnNull();
        $this->migrationsConfigurationSource->shouldReceive('isPlatformDependent')->once()->andReturn(false);

        $configuration = $this->factory->createConfiguration();
        $this->assertFalse($configuration->areMigrationsOrganizedByYear());
        $this->assertFalse($configuration->areMigrationsOrganizedByYearAndMonth());
        $this->assertEquals('Some\Name\Space', $configuration->getMigrationsNamespace());
        $this->assertEquals('column_name', $configuration->getMigrationsColumnName());
        $this->assertEquals('table_name', $configuration->getMigrationsTableName());
        $this->assertEquals('/some/dir', $configuration->getMigrationsDirectory());
    }

    /**
     * @test
     */
    public function create_organize_byyear()
    {
        $this->migrationsConfigurationSource->shouldReceive('getMode')->once()->andReturn(MigrationsConfigurationSource::MODE_BYYEAR);
        $this->migrationsConfigurationSource->shouldReceive('isPlatformDependent')->once()->andReturn(false);

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
        $this->migrationsConfigurationSource->shouldReceive('getMode')->once()->andReturn(MigrationsConfigurationSource::MODE_BYYEARANDMONTH);
        $this->migrationsConfigurationSource->shouldReceive('isPlatformDependent')->once()->andReturn(false);

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
        $this->migrationsConfigurationSource->shouldReceive('getMode')->once()->andReturnNull();
        $this->migrationsConfigurationSource->shouldReceive('isPlatformDependent')->once()->andReturn(true);

        $configuration = $this->factory->createConfiguration();
        $this->assertFalse($configuration->areMigrationsOrganizedByYear());
        $this->assertFalse($configuration->areMigrationsOrganizedByYearAndMonth());
        $this->assertEquals('/some/dir'.DIRECTORY_SEPARATOR.'sqlite', $configuration->getMigrationsDirectory());
    }
}
