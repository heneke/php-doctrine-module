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

namespace HHIT\Doctrine\ORM;

use Carbon\Carbon;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use HHIT\Doctrine\App\ORM\Entity\CarbonTestEntity;
use HHIT\Doctrine\App\ORM\Entity\SampleEntity;
use HHIT\Doctrine\DBAL\Contracts\DBALConnectionFactory;
use HHIT\Doctrine\ORM\Contracts\EntityManagerConfigurationFactory;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class EntityManagerDefaultFactoryTest extends TestCase
{
    /**
     * @var MockInterface
     */
    private $configurationFactory;

    /**
     * @var MockInterface
     */
    private $connectionFactory;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var EntityManagerDefaultFactory
     */
    private $factory;

    /**
     * @var string
     */
    private $oldTimezone;

    /**
     * @before
     */
    public function before()
    {
        $this->configurationFactory = \Mockery::mock(EntityManagerConfigurationFactory::class);
        $this->connectionFactory = \Mockery::mock(DBALConnectionFactory::class);
        $this->connectionFactory->shouldReceive('createConnection')->once()->andReturn(DriverManager::getConnection(['url' => 'sqlite:///:memory:']));
        $this->eventManager = \Mockery::mock(EventManager::class);
        $this->factory = new EntityManagerDefaultFactory($this->configurationFactory, $this->connectionFactory, $this->eventManager);

        $this->oldTimezone = date_default_timezone_get();
        date_default_timezone_set('Europe/Berlin');
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
        date_default_timezone_set($this->oldTimezone);
    }

    private function createTable(AbstractSchemaManager $manager)
    {
        if (!$manager->tablesExist('sample')) {
            $table = new Table('sample');
            $table->addColumn('id', 'integer', ['notnull' => true, 'autoincrement' => true]);
            $table->setPrimaryKey(['id']);
            $table->addColumn('value', 'string', ['length' => 255, 'notnull' => true]);
            $manager->createTable($table);
        }
    }

    /**
     * @test
     */
    public function create_annotation()
    {
        $this->configurationFactory->shouldReceive('createConfiguration')->once()->andReturn(Setup::createAnnotationMetadataConfiguration([__DIR__.'/../App/ORM/Entity'], true, null, null, false));
        $em = $this->factory->createEntityManager();
        $this->assertNotNull($em);
        $classNames = $em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        $this->assertCount(2, $classNames);
        $this->assertContains(CarbonTestEntity::class, $classNames);
        $this->assertContains(SampleEntity::class, $classNames);
        $this->basicSetup($em);
    }

    /**
     * @test
     */
    public function create_yaml()
    {
        $this->configurationFactory->shouldReceive('createConfiguration')->once()->andReturn(Setup::createYAMLMetadataConfiguration([__DIR__.'/../App/ORM/Entity'], true, null, null));
        $em = $this->factory->createEntityManager();
        $this->assertNotNull($em);
        $this->assertEquals([SampleEntity::class], $em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames());
        $this->basicSetup($em);
    }

    /**
     * @test
     */
    public function create_xml()
    {
        $this->configurationFactory->shouldReceive('createConfiguration')->once()->andReturn(Setup::createXMLMetadataConfiguration([__DIR__.'/../App/ORM/Entity'], true, null, null));
        $em = $this->factory->createEntityManager();
        $this->assertNotNull($em);
        $this->assertEquals([SampleEntity::class], $em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames());
        $this->basicSetup($em);
    }

    private function basicSetup(EntityManagerInterface $em)
    {
        $this->createTable($em->getConnection()->getSchemaManager());

        $e = new SampleEntity();
        $e->value = 'foo';
        $em->persist($e);
        $em->flush();
        $id = $e->id;

        $e = $em->find(SampleEntity::class, $id);
        $this->assertNotNull($e);
        $this->assertEquals('foo', $e->value);
        $e->value = 'bar';
        $em->merge($e);
        $em->flush();

        $e = $em->find(SampleEntity::class, $id);
        $this->assertNotNull($e);
        $this->assertEquals('bar', $e->value);
        $em->remove($e);
        $em->flush();

        $e = $em->find(SampleEntity::class, $id);
        $this->assertNull($e);
    }

    /**
     * @test
     */
    public function carbon()
    {
        $this->configurationFactory->shouldReceive('createConfiguration')->once()->andReturn(Setup::createAnnotationMetadataConfiguration([__DIR__.'/../App/ORM/Entity'], true, null, null, false));
        $em = $this->factory->createEntityManager();
        $connection = $em->getConnection();
        $manager = $connection->getSchemaManager();
        if (!$manager->tablesExist('carbon_test')) {
            $table = new Table('carbon_test');
            $table->addColumn('te_id', 'integer', ['notnull' => true, 'auto_increment' => true]);
            $table->addColumn('te_time', 'time', ['notnull' => false]);
            $table->addColumn('te_date', 'date', ['notnull' => false]);
            $table->addColumn('te_datetime', 'datetime', ['notnull' => false]);
            $table->addColumn('te_datetimetz', 'datetimetz', ['notnull' => false]);
            $table->setPrimaryKey(['te_id']);
            $manager->createTable($table);
        }

        $time = Carbon::create(2010, 1, 1, 11, 11, 0);
        $date = Carbon::create(2011, 1, 1);
        $datetime = Carbon::create(2012, 1, 1, 11, 11, 0);
        $datetimetz = Carbon::create(2013, 1, 1, 11, 11, 9, new \DateTimeZone('Europe/Athens'));

        $e1 = new CarbonTestEntity();
        $e1->time = $time;
        $e1->date = $date;
        $e1->datetime = $datetime;
        $e1->datetimetz = $datetimetz;

        $em->persist($e1);
        $em->flush();
        $em->clear();
        $id = $e1->id;

        $e1 = $em->find(CarbonTestEntity::class, $id);
        $this->assertNotNull($e1);
        foreach (['time', 'date', 'datetime', 'datetimetz'] as $key) {
            $this->assertInstanceOf(Carbon::class, $e1->$key);
        }
        foreach (['time', 'date', 'datetime'] as $key) {
            $this->assertEquals('Europe/Berlin', $e1->$key->tzName);
        }
        $this->assertEquals($time->format('H:i:s'), $e1->time->format('H:i:s'));
        $this->assertEquals($date->format('Y-m-d'), $e1->date->format('Y-m-d'));
        $this->assertEquals($datetime, $e1->datetime);

        // sqlite does not support datetimetz
        if ($em->getConnection()->getDatabasePlatform() instanceof SqlitePlatform) {
            $this->assertEquals('Europe/Berlin', $e1->datetimetz->tzName);
            $this->assertEquals($datetimetz->addHour(1), $e1->datetimetz);
        } else {
            $this->assertEquals('Europe/Athens', $e1->datetimetz->tzName);
            $this->assertEquals($datetimetz, $e1->datetimetz);
        }

        $e2 = new CarbonTestEntity();
        $em->persist($e2);
        $em->flush();
        $em->clear();
        $em->close();

        $id = $e2->id;

        $e2 = $em->find(CarbonTestEntity::class, $id);
        $this->assertNotNull($e2);
        foreach (['time', 'date', 'datetime', 'datetimetz'] as $key) {
            $this->assertNull($e2->$key);
        }
    }
}
