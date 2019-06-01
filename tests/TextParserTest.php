<?php

namespace aymanrb\UnstructuredTextParser\Tests;

include_once __DIR__ . '/../vendor/autoload.php';

use aymanrb\UnstructuredTextParser\Exception\InvalidTemplatesDirectoryException;
use aymanrb\UnstructuredTextParser\TextParser;
use PHPUnit\Framework\TestCase;

class TextParserTest extends TestCase
{
    public function testExceptionIsRaisedForInvalidConstructorArguments()
    {
        $this->expectException(InvalidTemplatesDirectoryException::class);
        new TextParser(__DIR__ . '/DirectoryThatNeverExists');

    }

    public function testTextParsingFailure()
    {
        $parser = new TextParser(__DIR__ . '/templates');
        $parseResults = $parser->parseText(file_get_contents(__DIR__ . '/test_txt_files/noMatch.txt'));

        $this->assertEmpty($parseResults->getParsedRawData());
    }

    public function testTextParsingSuccess()
    {
        $parser = new TextParser(__DIR__ . '/templates');
        $parsedValues = $parser->parseText(file_get_contents(__DIR__ . '/test_txt_files/t0TemplateMatch.txt'));
        $this->assertEquals(13, $parsedValues->countResults());
    }

    public function testSimilarityCheckFalseSelectsFirstMatchTemplateRatherBestFit()
    {
        $parser = new TextParser(__DIR__ . '/templates');
        $parsedValues = $parser->parseText(
            file_get_contents(__DIR__ . '/test_txt_files/webFeedback.html'));
        $this->assertEquals(1, $parsedValues->countResults());
    }

    public function testSimilarityCheckTrueSelectsBestFitTemplateRatherThanFirstMatch()
    {
        $parser = new TextParser(__DIR__ . '/templates');
        $parsedValues = $parser->parseText(
            file_get_contents(__DIR__ . '/test_txt_files/webFeedback.html'),
            true
        );
        $this->assertEquals(10, $parsedValues->countResults());
        $this->assertFalse($parsedValues->keyExists('theWholeMessageMatch'));
        $this->assertEquals('Mozilla', $parsedValues->get('browserCode'));

    }

    public function testTextParsingReturns()
    {
        $parser = new TextParser(__DIR__ . '/templates');
        $parsedValues = $parser->parseText(file_get_contents(__DIR__ . '/test_txt_files/t0TemplateMatch.txt'));

        //Make sure no html scripts are returned
        $this->assertEquals(
            $parsedValues->get('country'),
            htmlspecialchars($parsedValues->get('country'))
        );
        //Make sure data is trimmed on return
        $this->assertEquals('2', $parsedValues->get('children'));

        //Make sure data format and whitespaces are preserved
        $this->assertEquals(
            '11 - 10 - 2014',
            $parsedValues->get('arrival_date')
        );
    }
}
