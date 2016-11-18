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

use Doctrine\ORM\EntityManagerInterface;

class MigrationsConsoleHelper
{
    public static function createConfigurationFactory(EntityManagerInterface $entityManager, $fileName = 'doctrine.config.php', $subkey = 'migrations')
    {
        return new MigrationsDefaultConfigurationFactory(self::createConfigurationSource($fileName, $subkey), $entityManager);
    }

    private static function createConfigurationSource($fileName, $subkey)
    {
        $files = [getcwd().DIRECTORY_SEPARATOR.$fileName, getcwd().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$fileName];
        foreach ($files as $file) {
            if (file_exists($file)) {
                return new MigrationsFileConfigurationSource($file, $subkey);
            }
        }

        throw new \RuntimeException('Please configure Migrations with '.implode(' or ', $files).'!');
    }
}
