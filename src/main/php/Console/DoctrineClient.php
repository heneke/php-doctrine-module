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

namespace HHIT\Doctrine\Console;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\OutputWriter;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Doctrine\Migrations\Tools\Console\Helper\ConfigurationHelper;
use Doctrine\DBAL\Tools\Console\Command\ImportCommand;
use Doctrine\DBAL\Tools\Console\Command\ReservedWordsCommand;
use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Command\ClearCache\CollectionRegionCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\EntityRegionCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryRegionCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand;
use Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand;
use Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand;
use Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand;
use Doctrine\ORM\Tools\Console\Command\InfoCommand;
use Doctrine\ORM\Tools\Console\Command\MappingDescribeCommand;
use Doctrine\ORM\Tools\Console\Command\RunDqlCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use HHIT\Doctrine\Fixtures\Console\Commands\LoadORMFixturesCommand;
use HHIT\Doctrine\Fixtures\Console\Commands\PurgeORMFixturesCommand;
use HHIT\Doctrine\Migrations\MigrationsConsoleHelper;
use HHIT\Doctrine\ORM\ORMConsoleHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class DoctrineClient
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Application
     */
    private $application;

    private function __construct(EntityManagerInterface $entityManager, Configuration $configuration, Application $application)
    {
        $this->entityManager = $entityManager;
        $this->configuration = $configuration;
        $this->application = $application;
    }

    /**
     * @param InputInterface|null  $input
     * @param OutputInterface|null $output
     * @codeCoverageIgnore
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if ($input == null) {
            $input = new ArgvInput();
        }
        if ($output == null) {
            $output = new ConsoleOutput();
            $this->configuration->setOutputWriter(new OutputWriter(function ($message) use ($output) {
                $output->writeln($message);
            }));
        }
        $this->application->run($input, $output);
    }

    public static function create($fileName = 'doctrine.config.php', $subKeyOrm = 'orm', $subKeyDbal = 'dbal', $subKeyMigrations = 'migrations', OutputInterface $output = null)
    {
        AnnotationRegistry::registerLoader('class_exists');
        $entityManagerConfigurationSource = ORMConsoleHelper::createEntityManagerConfigurationSource($fileName, $subKeyOrm);
        $entityManager = ORMConsoleHelper::createEntityManager($fileName, $subKeyOrm, $subKeyDbal);
        $configuration = MigrationsConsoleHelper::createConfigurationFactory($entityManagerConfigurationSource, $entityManager, $fileName, $subKeyMigrations)->createConfiguration($output);
        $application = ConsoleRunner::createApplication(self::createHelperSet($entityManager, $configuration), self::getCommands());

        return new self($entityManager, $configuration, $application);
    }

    public static function createHelperSet(EntityManagerInterface $entityManager, Configuration $configuration)
    {
        return new HelperSet([
            'db' => self::createConnectionHelper($entityManager),
            'em' => self::createEntityManagerHelper($entityManager),
            'configuration' => self::createConfigurationHelper($entityManager, $configuration),
            'question' => self::createQuestionHelper(),
        ]);
    }

    public static function getCommands()
    {
        $commands = [];
        foreach (self::getCommandClasses() as $commandClass) {
            $commands[] = new $commandClass();
        }

        return $commands;
    }

    public static function getCommandClasses()
    {
        return [
            // dbal
            ImportCommand::class,
            ReservedWordsCommand::class,
            RunSqlCommand::class,

            // orm:clear-cache
            CollectionRegionCommand::class,
            EntityRegionCommand::class,
            MetadataCommand::class,
            QueryCommand::class,
            QueryRegionCommand::class,
            ResultCommand::class,

            // orm:schema-tool
            CreateCommand::class,
            DropCommand::class,
            UpdateCommand::class,

            // orm
            ConvertDoctrine1SchemaCommand::class,
            ConvertMappingCommand::class,
            EnsureProductionSettingsCommand::class,
            GenerateEntitiesCommand::class,
            GenerateProxiesCommand::class,
            GenerateRepositoriesCommand::class,
            InfoCommand::class,
            MappingDescribeCommand::class,
            RunDqlCommand::class,
            ValidateSchemaCommand::class,

            // migrations
            DiffCommand::class,
            ExecuteCommand::class,
            GenerateCommand::class,
            LatestCommand::class,
            MigrateCommand::class,
            StatusCommand::class,
            VersionCommand::class,

            // fixtures
            PurgeORMFixturesCommand::class,
            LoadORMFixturesCommand::class,
        ];
    }

    public static function createConfigurationHelper(EntityManagerInterface $entityManager, Configuration $configuration)
    {
        return new ConfigurationHelper($entityManager->getConnection(), $configuration);
    }

    public static function createConnectionHelper(EntityManagerInterface $entityManager)
    {
        return new ConnectionHelper($entityManager->getConnection());
    }

    public static function createEntityManagerHelper(EntityManagerInterface $entityManager)
    {
        return new EntityManagerHelper($entityManager);
    }

    public static function createQuestionHelper()
    {
        return new QuestionHelper();
    }
}
