<?php

declare(strict_types = 1);

namespace aymanrb\UnstructuredTextParser;

use aymanrb\UnstructuredTextParser\Exception\InvalidParsedDataKeyException;

class ParseResult
{
    private array $parsedRawData = [];

    private ?string $appliedTemplateFile;

    public function getParsedRawData(): array
    {
        return $this->parsedRawData;
    }

    public function setParsedRawData(array $parsedRawData): void
    {
        $this->parsedRawData = $parsedRawData;
        $this->cleanData();
    }

    public function getAppliedTemplateFile(): ?string
    {
        return $this->appliedTemplateFile;
    }

    public function setAppliedTemplateFile(string $appliedTemplateFile): void
    {
        $this->appliedTemplateFile = $appliedTemplateFile;
    }

    public function countResults(): int
    {
        return count($this->parsedRawData);
    }

    public function keyExists(string $key): bool
    {
        return array_key_exists($key, $this->parsedRawData);
    }

    public function get(string $resultDataKey, bool $failOnUndefinedKey = false): ?string
    {
        if (!$this->keyExists($resultDataKey)) {
            if (!$failOnUndefinedKey) {
                return null;
            }

            throw new InvalidParsedDataKeyException('Undefined results key: ' . $resultDataKey);
        }

        return $this->parsedRawData[$resultDataKey];
    }

    private function cleanData(): void
    {
        foreach ($this->parsedRawData as $key => $value) {
            $this->parsedRawData[$key] = $this->cleanElement($value);
        }
    }

    private function cleanElement(string $value): string
    {
        return trim(strip_tags($value));
    }
}
