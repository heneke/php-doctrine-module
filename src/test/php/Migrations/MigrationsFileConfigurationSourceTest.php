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

namespace HHIT\Doctrine\Migrations;

use PHPUnit\Framework\TestCase;

class MigrationsFileConfigurationSourceTest extends TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage File required
     */
    public function no_file()
    {
        new MigrationsFileConfigurationSource(null);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage does not exist
     */
    public function nonexisting_file()
    {
        new MigrationsFileConfigurationSource(__FILE__.'.does.not.exist');
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    public function not_array()
    {
        new MigrationsFileConfigurationSource(__DIR__.'/../../configs/invalid.config.php');
    }

    /**
     * @test
     */
    public function full()
    {
        $configuration = new MigrationsFileConfigurationSource(__DIR__.'/../../configs/migrations.full.config.php');
        $this->assertEquals(MigrationsFileConfigurationSource::MODE_BYYEAR, $configuration->getMode());
        $this->assertEquals('version_column', $configuration->getColumnName());
        $this->assertEquals('version_table', $configuration->getTableName());
        $this->assertTrue($configuration->isPlatformDependent());
        $this->assertEquals('Some\Name\Space', $configuration->getNamespace());
        $this->assertEquals('/some/dir', $configuration->getOutputDirectory());
    }

    /**
     * @test
     */
    public function minimal()
    {
        $configuration = new MigrationsFileConfigurationSource(__DIR__.'/../../configs/migrations.minimal.config.php');
        $this->assertEquals(MigrationsFileConfigurationSource::MODE_DEFAULT, $configuration->getMode());
        $this->assertEquals('version', $configuration->getColumnName());
        $this->assertEquals('doctrine_migrations_version', $configuration->getTableName());
        $this->assertFalse($configuration->isPlatformDependent());
        $this->assertEquals('Migrations', $configuration->getNamespace());
        $this->assertEquals(getcwd(), $configuration->getOutputDirectory());
    }
}
