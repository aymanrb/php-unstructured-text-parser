<?php
include_once __DIR__ . '/../vendor/autoload.php';

use aymanrb\UnstructuredTextParser\TextParser;

try {
    $parser = new TextParser(__DIR__ . '/templates');
    $textFiles = new FilesystemIterator(__DIR__ . '/test_txt_files');

    foreach ($textFiles as $txtFileObj) {
        if ($txtFileObj->getExtension() === 'txt') {
            echo $txtFileObj->getFilename() . PHP_EOL;

            $parseResults = $parser->parseFileContent($txtFileObj->getPathname(), true);

            print_r($parseResults->getParsedRawData());

            if ($parseResults->getAppliedTemplateFile()) {
                echo 'Matched Template: ' . $parseResults->getAppliedTemplateFile() . PHP_EOL;
            }
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
