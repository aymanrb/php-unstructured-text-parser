<?php

namespace aymanrb\UnstructuredTextParser;

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
        $this->parseResults = new ParseResult();
    }

    public function parseText(string $text, bool $findMatchingTemplate = false): array
    {
        $this->logger->info(sprintf('Parsing: %s', $text));

        $text = $this->prepareText($text);
        $parsableTemplates = $this->templatesHelper->getTemplates($text, $findMatchingTemplate);

        foreach ($parsableTemplates as $templatePath => $templatePattern) {
            $this->logger->debug(sprintf('Parsing against template: %s', $templatePath));

            $this->extractData($text, $templatePattern);
        }

        $this->logger->info(sprintf('Data extracted: %s', json_encode($this->parseResults->getParsedData())));

        return $this->parseResults->getParsedData();
    }

    private function prepareText(string $text): string
    {
        //Remove all multiple whitespaces and replace it with single space
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    private function extractData(string $text, string $template): void
    {
        //Extract the text based on the provided template using REGEX
        preg_match('/' . $template . '/s', $text, $matches);

        //Extract only the named parameters from the matched regex array
        $keys = array_filter(array_keys($matches), 'is_string');
        $matches = array_intersect_key($matches, array_flip($keys));

        if (empty($matches)) {
            return;
        }

        $this->parseResults->setParsedData($matches);
    }
}
