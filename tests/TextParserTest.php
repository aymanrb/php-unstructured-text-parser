<?php

namespace aymanrb\UnstructuredTextParser\Tests;

include_once __DIR__ . '/../vendor/autoload.php';

use aymanrb\UnstructuredTextParser\TextParser;
use PHPUnit\Framework\TestCase;
use Exception;

class TextParserTest extends TestCase {


    /**
     * @covers              TextParser::__construct
     * @uses                TextParser::setTemplatesDir
     * @expectedException   Exception
     */
    public function testExceptionIsRaisedForInvalidConstructorArguments(){
    	new TextParser(__DIR__ . '/DirectoryThatNeverExists');
    }


    /**
     * @covers	TextParser::parseText
     * @uses    TextParser::__construct
     * @uses    TextParser::setTemplatesDir
     * @uses    TextParser::findTemplate
     * @uses    TextParser::prepareTemplate
     * @uses    TextParser::prepareText
     * @uses    TextParser::prepareText
     * @uses    TextParser::extractData
     */
  	public function testTextParsingFailure(){
  		$parser = new TextParser(__DIR__ . '/templates');
  		$this->assertFalse($parser->parseText(file_get_contents(__DIR__ . '/test_txt_files/noTemplate.txt')));
  	}

    /**
     * @covers	TextParser::parseText
     * @uses    TextParser::__construct
     * @uses    TextParser::setTemplatesDir
     * @uses    TextParser::findTemplate
     * @uses    TextParser::prepareTemplate
     * @uses    TextParser::prepareText
     * @uses    TextParser::prepareText
     * @uses    TextParser::extractData
     */
  	public function testTextParsingSuccess(){
  		$parser = new TextParser(__DIR__ . '/templates');
  		$parsedValues = $parser->parseText(file_get_contents(__DIR__ . '/test_txt_files/success.txt'));
  		$this->assertEquals(13, count($parsedValues));
  	}

    /**
     * @covers	TextParser::parseText
     * @uses    TextParser::__construct
     * @uses    TextParser::setTemplatesDir
     * @uses    TextParser::findTemplate
     * @uses    TextParser::prepareTemplate
     * @uses    TextParser::prepareText
     * @uses    TextParser::prepareText
     * @uses    TextParser::extractData
     */
  	public function testTextParsingReturns(){
  		$parser = new TextParser(__DIR__ . '/templates');
  		$parsedValues = $parser->parseText(file_get_contents(__DIR__ . '/test_txt_files/success.txt'));

  		$this->assertEquals($parsedValues['country'], htmlspecialchars($parsedValues['country'])); //Make sure no html scripts are returned
  		$this->assertEquals('2', $parsedValues['children']); //Make sure data is trimed on return
  	}
}
