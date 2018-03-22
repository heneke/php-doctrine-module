<?php

$finder = PhpCsFixer\Finder::create()
    ->in('src/main/php')->in('src/test/php');

return PhpCsFixer\Config::create()
    ->setFinder($finder);
