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

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Migration;
use PHPUnit\Framework\TestCase;

class MigrationMultiDirectoryConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function multipleDirectories()
    {
        $connection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $configuration = new Configuration($connection);

        $configuration->setMigrationsAreOrganizedByYear(false);
        $configuration->setMigrationsAreOrganizedByYearAndMonth(false);
        $configuration->setMigrationsDirectory('/some/dir');
        $configuration->setMigrationsNamespace('Some\Name\Space');

        $dir1 = __DIR__.'/../../multi_migrations/unit1';
        $dir2 = __DIR__.'/../../multi_migrations/unit2';

        $configuration->registerMigrationsFromDirectory($dir1);
        $configuration->registerMigrationsFromDirectory($dir2);

        $expected = ['20160921103507', '20160921103508'];

        $this->assertEquals($expected, $configuration->getAvailableVersions());
        $this->assertEquals('0', $configuration->getCurrentVersion());
        $this->assertEquals('20160921103508', $configuration->getLatestVersion());

        $migration = new Migration($configuration);
        $migration->migrate($configuration->getLatestVersion());

        $manager = $connection->getSchemaManager();
        $this->assertTrue($manager->tablesExist('carbon_test_1'));
        $this->assertTrue($manager->tablesExist('carbon_test_2'));
        $this->assertTrue($manager->tablesExist('sample_1'));
        $this->assertTrue($manager->tablesExist('sample_2'));
    }
}
