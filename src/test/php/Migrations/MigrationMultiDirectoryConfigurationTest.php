<?php

namespace HHIT\Doctrine\Migrations;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Migration;

class MigrationMultiDirectoryConfigurationTest extends \PHPUnit_Framework_TestCase
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
