<?php

namespace aymanrb\UnstructuredTextParser\Tests;

include_once __DIR__ . '/../vendor/autoload.php';

use aymanrb\UnstructuredTextParser\TextParser;
use Exception;
use PHPUnit\Framework\TestCase;

class TextParserTest extends TestCase
{
    /**
     * @expectedException   Exception
     */
    public function testExceptionIsRaisedForInvalidConstructorArguments()
    {
        new TextParser(__DIR__ . '/DirectoryThatNeverExists');
    }

    public function testTextParsingFailure()
    {
        $parser = new TextParser(__DIR__ . '/templates');
        $this->assertEmpty(
            $parser->parseText(file_get_contents(__DIR__ . '/test_txt_files/noTemplate.txt'))
        );
    }

    public function testTextParsingSuccess()
    {
        $parser = new TextParser(__DIR__ . '/templates');
        $parsedValues = $parser->parseText(file_get_contents(__DIR__ . '/test_txt_files/success.txt'));
        $this->assertEquals(13, count($parsedValues));
    }

    public function testTextParsingWithSimilarityCheckSuccess()
    {
        $parser = new TextParser(__DIR__ . '/templates');
        $parsedValues = $parser->parseText(
            file_get_contents(__DIR__ . '/test_txt_files/success.txt'),
            true
        );
        $this->assertEquals(13, count($parsedValues));
    }

    public function testTextParsingReturns()
    {
        $parser = new TextParser(__DIR__ . '/templates');
        $parsedValues = $parser->parseText(file_get_contents(__DIR__ . '/test_txt_files/success.txt'));

        $this->assertEquals(
            $parsedValues['country'],
            htmlspecialchars($parsedValues['country'])
        ); //Make sure no html scripts are returned
        $this->assertEquals('2', $parsedValues['children']); //Make sure data is trimmed on return
        $this->assertEquals(
            '11 - 10 - 2014',
            $parsedValues['arrival_date']
        ); //Make sure data format and whitespaces are preserved
    }
}
