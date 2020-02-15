<?php

namespace aymanrb\UnstructuredTextParser;

use aymanrb\UnstructuredTextParser\Exception\InvalidParseFileException;
use aymanrb\UnstructuredTextParser\Helper\TemplatesHelper;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class TextParser
{
    use LoggerAwareTrait;

    /** @var TemplatesHelper; Templates Directory Iterator */
    private $templatesHelper;

    /** @var ParseResult; Parsed data results */
    private $parseResults;


    public function __construct(string $templatesDir, LoggerInterface $logger = null)
    {
        if (empty($logger)) {
            $logger = new NullLogger();
        }

        $this->setLogger($logger);
        $this->templatesHelper = new TemplatesHelper($templatesDir);
        $this->resetParseResults();
    }

    public function parseFileContent(string $filePath, bool $findMatchingTemplate = false): ParseResult
    {
        if (!is_file($filePath)) {
            throw new InvalidParseFileException($filePath);
        }

        return $this->parseText(file_get_contents($filePath), $findMatchingTemplate);
    }

    public function parseText(string $text, bool $findMatchingTemplate = false): ParseResult
    {
        $this->resetParseResults();

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
