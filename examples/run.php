<?php
include_once __DIR__ . '/../vendor/autoload.php';

use aymanrb\UnstructuredTextParser\TextParser;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

try {
    $logger = new Logger('text-parser');
    $logger->pushHandler(new StreamHandler('logs/text-parser.log', Logger::DEBUG));

    $parser = new TextParser(__DIR__ . '/templates', $logger);
    $textFiles = new DirectoryIterator(__DIR__ . '/test_txt_files');

    foreach ($textFiles as $txtFileObj) {
        if ($txtFileObj->getExtension() == 'txt') {
            echo $txtFileObj->getFilename() . PHP_EOL;

            echo print_r(
                $parser->parseText(
                    file_get_contents($txtFileObj->getPathname())
                )
            );
        }
    }

} catch (Exception $e) {
    echo $e->getMessage();
}
