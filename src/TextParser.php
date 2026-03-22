<?php

declare(strict_types = 1);

namespace aymanrb\UnstructuredTextParser;

use aymanrb\UnstructuredTextParser\Exception\InvalidParseFileException;
use aymanrb\UnstructuredTextParser\Helper\TemplatesHelper;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class TextParser
{
    use LoggerAwareTrait;

    private readonly TemplatesHelper $templatesHelper;

    private ParseResult $parseResults;

    public function __construct(string $templatesDir, ?LoggerInterface $logger = null)
    {
        $this->setLogger($logger ?? new NullLogger());
        $this->templatesHelper = new TemplatesHelper($templatesDir);
        $this->resetParseResults();
    }

    public function parseFileContent(string $filePath, bool $findMatchingTemplate = false): ParseResult
    {
        if (!is_file($filePath)) {
            throw new InvalidParseFileException($filePath);
        }

        $fileContents = file_get_contents($filePath);

        if ($fileContents === false) {
            throw new InvalidParseFileException($filePath);
        }

        return $this->parseText($fileContents, $findMatchingTemplate);
    }

    public function parseText(string $text, bool $findMatchingTemplate = false): ParseResult
    {
        $this->resetParseResults();

        if (trim($text) === '') {
            $this->logger->warning('parseText called with empty or whitespace-only text, skipping parsing');

            return $this->parseResults;
        }

        $parsableTemplates = $this->templatesHelper->getTemplates($text, $findMatchingTemplate);

        foreach ($parsableTemplates as $templatePath => $templatePattern) {
            $this->logger->debug(sprintf('Parsing against template: %s', $templatePath));

            if ($this->extractData($text, $templatePattern)) {
                $this->parseResults->setAppliedTemplateFile($templatePath);
            }
        }

        $this->logger->info(sprintf('Data extracted: %s', json_encode($this->parseResults->getParsedRawData())));

        return $this->parseResults;
    }

    public function getParseResults(): ParseResult
    {
        return $this->parseResults;
    }

    private function extractData(string $text, string $template): bool
    {
        //Extract the text based on the provided template using REGEX
        preg_match('/' . $template . '/s', $text, $matches);

        //Extract only the named parameters from the matched regex array
        $keys = array_filter(array_keys($matches), 'is_string');
        $matches = array_intersect_key($matches, array_flip($keys));

        if (empty($matches)) {
            return false;
        }

        $this->parseResults->setParsedRawData($matches);

        return true;
    }

    private function resetParseResults(): void
    {
        $this->parseResults = new ParseResult();
    }
}
