<?php

namespace aymanrb\UnstructuredTextParser\Tests;

include_once __DIR__ . '/../vendor/autoload.php';

use aymanrb\UnstructuredTextParser\Exception\InvalidParsedDataKeyException;
use aymanrb\UnstructuredTextParser\ParseResult;
use PHPUnit\Framework\TestCase;

class ParseResultTest extends TestCase
{
    private function getNewParseResultObject($fillWithData = false): ParseResult
    {
        $parseResult = new ParseResult();

        if ($fillWithData) {
            $parseResult->setParsedRawData(
                [
                    'resultsKey' => 'resultsValue     ',
                    'foundKey' => 'parsedContent   <b>SometextInBold</b>',
                    'date' => '2019-01-01',
                    'time' => ' 20:11',
                ]
            );
        }

        return $parseResult;
    }

    public function testGetParsedRawData()
    {
        $parseResult = $this->getNewParseResultObject();

        $this->assertIsArray($parseResult->getParsedRawData());
        $this->assertEmpty($parseResult->getParsedRawData());
    }

    public function testSetParsedRawData()
    {
        $parseResult = $this->getNewParseResultObject(true);

        $this->assertIsArray($parseResult->getParsedRawData());
        $this->assertNotEmpty($parseResult->getParsedRawData());
        $this->assertArrayHasKey('resultsKey', $parseResult->getParsedRawData());
        $this->assertArrayHasKey('foundKey', $parseResult->getParsedRawData());
    }

    public function testSetParsedRawDataCleansContent()
    {
        $parseResult = $this->getNewParseResultObject(true);

        $resultsArray = $parseResult->getParsedRawData();

        $this->assertEquals('resultsValue', $resultsArray['resultsKey']);
        $this->assertEquals('parsedContent   SometextInBold', $resultsArray['foundKey']);
        $this->assertEquals('20:11', $resultsArray['time']);
    }

    public function testAppliedTemplateFileSetterAndGetter()
    {
        $parseResult = $this->getNewParseResultObject();

        $this->assertEmpty($parseResult->getAppliedTemplateFile());

        $matchedTemplatePath = 'path/to/matched/Template.txt';
        $parseResult->setAppliedTemplateFile($matchedTemplatePath);

        $this->assertNotEmpty($parseResult->getAppliedTemplateFile());
        $this->assertEquals($matchedTemplatePath, $parseResult->getAppliedTemplateFile());
    }

    public function testCountResults()
    {
        $parseResult = $this->getNewParseResultObject(true);

        $this->assertEquals(4, $parseResult->countResults());
    }

    public function testKeyExists()
    {
        $parseResult = $this->getNewParseResultObject(true);

        $this->assertTrue($parseResult->keyExists('foundKey'));
        $this->assertTrue($parseResult->keyExists('resultsKey'));
        $this->assertFalse($parseResult->keyExists('AKeyWeNeverFound'));
    }

    public function testGetResultKey()
    {
        $parseResult = $this->getNewParseResultObject(true);
        $this->assertEquals('resultsValue', $parseResult->get('resultsKey'));
        $this->assertEquals('parsedContent   SometextInBold', $parseResult->get('foundKey'));
        $this->assertEquals('2019-01-01', $parseResult->get('date'));
        $this->assertEquals('20:11', $parseResult->get('time'));
        $this->assertNull($parseResult->get('AKeyWeNeverFound'));
    }

    public function testStrictGetResultKeyThrowsInvalidKeyException()
    {
        $parseResult = $this->getNewParseResultObject(true);
        $this->assertEquals('resultsValue', $parseResult->get('resultsKey', true));


        $this->expectException(InvalidParsedDataKeyException::class);
        $parseResult->get('AKeyWeNeverFound', true);
    }
}
