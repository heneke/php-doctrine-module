<?php

$found = false;
foreach ([__DIR__ . '/../../../autoload.php', __DIR__ . '/../vendor/autoload.php'] as $file) {
    if (file_exists($file)) {
        require_once($file);
        $found = true;
        break;
    }
}
if (!$found) {
    throw new RuntimeException('Could not locate autoload.php!');
}

\HHIT\Doctrine\Console\DoctrineClient::create()->run();