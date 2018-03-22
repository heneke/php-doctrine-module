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

namespace HHIT\Doctrine\ORM;

use HHIT\Doctrine\App\ORM\SamplePersistenceUnit;
use HHIT\Doctrine\ORM\Contracts\EntityManagerConfigurationSource;
use PHPUnit\Framework\TestCase;

class EntityManagerFileConfigurationSourceTest extends TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage File required
     */
    public function no_file()
    {
        new EntityManagerFileConfigurationSource(null);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage does not exist
     */
    public function noexisting_file()
    {
        new EntityManagerFileConfigurationSource(__FILE__.'.does.not.exist');
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    public function not_array()
    {
        new EntityManagerFileConfigurationSource(__DIR__.'/../../configs/invalid.config.php');
    }

    /**
     * @test
     */
    public function full()
    {
        $source = new EntityManagerFileConfigurationSource(__DIR__.'/../../configs/orm.full.config.php');
        $this->assertEquals(EntityManagerConfigurationSource::METADATA_DRIVER_ANNOTATION, $source->getMetadataDriver());
        $this->assertEquals([SamplePersistenceUnit::class], $source->getPersistenceUnits());
        $this->assertEquals('/tmp', $source->getProxyDir());
        $this->assertTrue($source->isDevMode());
        $this->assertTrue($source->isUseSimpleAnnotationReader());
    }

    /**
     * @test
     */
    public function minimal()
    {
        $source = new EntityManagerFileConfigurationSource(__DIR__.'/../../configs/orm.minimal.config.php');
        $this->assertEquals(EntityManagerConfigurationSource::METADATA_DRIVER_ANNOTATION, $source->getMetadataDriver());
        $this->assertEquals([SamplePersistenceUnit::class], $source->getPersistenceUnits());
        $this->assertNull($source->getProxyDir());
        $this->assertFalse($source->isDevMode());
        $this->assertFalse($source->isUseSimpleAnnotationReader());
    }

    /**
     * @test
     * @expectedException \HHIT\Doctrine\ORM\InvalidConfigurationException
     * @expectedExceptionMessage driver required
     */
    public function no_driver()
    {
        (new EntityManagerFileConfigurationSource(__DIR__.'/../../configs/orm.nodriver.config.php'))->getMetadataDriver();
    }

    /**
     * @test
     * @expectedException \HHIT\Doctrine\ORM\InvalidConfigurationException
     * @expectedExceptionMessage units required
     */
    public function no_units()
    {
        (new EntityManagerFileConfigurationSource(__DIR__.'/../../configs/orm.nounits.config.php'))->getPersistenceUnits();
    }
}
