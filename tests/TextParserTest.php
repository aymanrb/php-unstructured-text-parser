<?php

namespace aymanrb\UnstructuredTextParser\Tests;

include_once __DIR__ . '/../vendor/autoload.php';

use aymanrb\UnstructuredTextParser\Exception\InvalidParseFileException;
use aymanrb\UnstructuredTextParser\Exception\InvalidTemplatesDirectoryException;
use aymanrb\UnstructuredTextParser\TextParser;
use PHPUnit\Framework\TestCase;

class TextParserTest extends TestCase
{
    public function testExceptionIsRaisedForInvalidConstructorArguments()
    {
        $this->expectException(InvalidTemplatesDirectoryException::class);
        new TextParser(__DIR__ . '/DirectoryThatDoesNotExist');
    }

    public function testTextParsingFailure()
    {
        $parser = $this->getTemplatesParser();
        $parser->parseText('Some Text that can not be matched against a template');

        $this->assertEmpty($parser->getParseResults()->getParsedRawData());
    }

    public function testTextParsingResetsPreviousMatch()
    {
        $parser = $this->getTemplatesParser();
        $parser->parseFileContent(__DIR__ . '/test_txt_files/t0TemplateMatch.txt');
        $this->assertEquals(13, $parser->getParseResults()->countResults());

        $parser->parseFileContent(__DIR__ . '/test_txt_files/noMatch.txt');
        $this->assertEmpty($parser->getParseResults()->getParsedRawData());
    }

    public function testTextParsingSuccess()
    {
        $parser = $this->getTemplatesParser();
        $parser->parseFileContent(__DIR__ . '/test_txt_files/t0TemplateMatch.txt');
        $this->assertEquals(13, $parser->getParseResults()->countResults());
    }

    public function testSimilarityCheckFalseSelectsFirstMatchTemplateRatherBestFit()
    {
        $parser = $this->getTemplatesParser();
        $parseResults = $parser->parseFileContent(__DIR__ . '/test_txt_files/webFeedback.html');
        $this->assertEquals(1, $parseResults->countResults());
        $this->assertTrue($parseResults->keyExists('theWholeMessageMatch'));
    }

    public function testSimilarityCheckTrueSelectsBestFitTemplateRatherThanFirstMatch()
    {
        $parser = $this->getTemplatesParser();
        $parseResults = $parser->parseFileContent(
            __DIR__ . '/test_txt_files/webFeedback.html',
            true
        );
        $this->assertEquals(10, $parseResults->countResults());
        $this->assertFalse($parseResults->keyExists('theWholeMessageMatch'));
        $this->assertEquals('Mozilla', $parseResults->get('browserCode'));
    }

    public function testTextParsingReturns()
    {
        $parser = $this->getTemplatesParser();
        $parseResults = $parser->parseFileContent(__DIR__ . '/test_txt_files/t0TemplateMatch.txt');

        //Make sure no html scripts are returned
        $this->assertEquals(
            $parseResults->get('country'),
            htmlspecialchars($parseResults->get('country'))
        );
        //Make sure data is trimmed on return
        $this->assertEquals('2', $parseResults->get('children'));

        //Make sure data format and whitespaces are preserved
        $this->assertEquals(
            '11 - 10 - 2014',
            $parseResults->get('arrival_date')
        );
    }

    public function testParseInvalidFileContentException()
    {
        $parser = $this->getTemplatesParser();
        $this->expectException(InvalidParseFileException::class);
        $parser->parseFileContent(__DIR__ . '/test_txt_files/unknown.txt');
    }

    private function getTemplatesParser(): TextParser
    {
        return new TextParser(__DIR__ . '/templates');
    }
}
