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

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use HHIT\Doctrine\App\ORM\SamplePersistenceUnit;
use HHIT\Doctrine\ORM\Contracts\EntityManagerConfigurationSource;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class EntityManagerDefaultConfigurationFactoryTest extends TestCase
{
    /**
     * @var MockInterface
     */
    private $source;

    /**
     * @var EntityManagerDefaultConfigurationFactory
     */
    private $factory;

    /**
     * @before
     */
    public function before()
    {
        $this->source = \Mockery::mock(EntityManagerConfigurationSource::class);
        $this->factory = new EntityManagerDefaultConfigurationFactory($this->source);
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
    public function annotation()
    {
        $this->source->shouldReceive('getMetadataDriver')->once()->andReturn(EntityManagerConfigurationSource::METADATA_DRIVER_ANNOTATION)
            ->shouldReceive('getPersistenceUnits')->once()->andReturn([SamplePersistenceUnit::class])
            ->shouldReceive('isDevMode')->once()->andReturn(false)
            ->shouldReceive('getProxyDir')->once()->andReturn(null)
            ->shouldReceive('isUseSimpleAnnotationReader')->once()->andReturn(false);
        $config = $this->factory->createConfiguration();
        $this->assertNotNull($config);
        $this->assertTrue($config->getMetadataDriverImpl() instanceof AnnotationDriver);
    }

    /**
     * @test
     */
    public function yaml()
    {
        $this->source->shouldReceive('getMetadataDriver')->once()->andReturn(EntityManagerConfigurationSource::METADATA_DRIVER_YAML)
            ->shouldReceive('getPersistenceUnits')->once()->andReturn([SamplePersistenceUnit::class])
            ->shouldReceive('isDevMode')->once()->andReturn(false)
            ->shouldReceive('getProxyDir')->once()->andReturn(null)
            ->shouldReceive('isUseSimpleAnnotationReader')->never();
        $config = $this->factory->createConfiguration();
        $this->assertNotNull($config);
        $this->assertTrue($config->getMetadataDriverImpl() instanceof YamlDriver);
    }

    /**
     * @test
     */
    public function xml()
    {
        $this->source->shouldReceive('getMetadataDriver')->once()->andReturn(EntityManagerConfigurationSource::METADATA_DRIVER_XML)
            ->shouldReceive('getPersistenceUnits')->once()->andReturn([SamplePersistenceUnit::class])
            ->shouldReceive('isDevMode')->once()->andReturn(false)
            ->shouldReceive('getProxyDir')->once()->andReturn(null)
            ->shouldReceive('isUseSimpleAnnotationReader')->never();
        $config = $this->factory->createConfiguration();
        $this->assertNotNull($config);
        $this->assertTrue($config->getMetadataDriverImpl() instanceof XmlDriver);
    }

    /**
     * @test
     * @expectedException \HHIT\Doctrine\ORM\InvalidConfigurationException
     * @expectedExceptionMessage unknown_driver is not supported
     */
    public function driver_invald()
    {
        $this->source->shouldReceive('getMetadataDriver')->andReturn('unknown_driver');
        $this->factory->createConfiguration();
    }
}
