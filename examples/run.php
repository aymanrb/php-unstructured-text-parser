<?php
include_once __DIR__ . '/../vendor/autoload.php';

use aymanrb\UnstructuredTextParser\TextParser;

try {
    $parser = new TextParser(__DIR__ . '/templates');
    $textFiles = new DirectoryIterator(__DIR__ . '/test_txt_files');

    foreach ($textFiles as $txtFileObj) {
        if ($txtFileObj->getExtension() == 'txt') {
            echo $txtFileObj->getFilename() . PHP_EOL;

            print_r(
                $parser->parseText(
                    file_get_contents($txtFileObj->getPathname())
                )
            );
        }
    }

} catch (Exception $e) {
    echo $e->getMessage();
}
