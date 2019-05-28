<?php

namespace aymanrb\UnstructuredTextParser;

use aymanrb\UnstructuredTextParser\Exception\InvalidParsedDataKeyException;

class ParseResult
{
    private $parsedData = [];


    public function getParsedData(): array
    {
        return $this->parsedData;
    }

    public function setParsedData(array $parsedData)
    {
        $this->parsedData = $parsedData;
        $this->cleanData();
    }

    public function keyExists($key)
    {
        return array_key_exists($key, $this->parsedData);
    }

    public function __get(string $resultDataKey)
    {
        if (!$this->keyExists($resultDataKey)) {
            throw new InvalidParsedDataKeyException('Undefined results key: ' . $resultDataKey);
        }

        return $this->parsedData[$resultDataKey];
    }

    private function cleanData()
    {
        foreach ($this->parsedData as $key => $value) {
            $this->parsedData[$key] = $this->cleanElement($value);
        }
    }

    private function cleanElement(string $value): string
    {
        return trim(strip_tags($value));
    }
}