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

        $this->logger->debug(sprintf('Parsing file: %s (%d bytes)', $filePath, strlen($fileContents)));

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

        $this->logger->debug(sprintf(
            'Attempting to parse %d character(s) against %d template(s) [similarity=%s]',
            strlen($text),
            count($parsableTemplates),
            $findMatchingTemplate ? 'on' : 'off'
        ));

        foreach ($parsableTemplates as $templatePath => $templatePattern) {
            $this->logger->debug(sprintf('Trying template: %s', basename($templatePath)));

            if ($this->extractData($text, $templatePattern)) {
                $this->parseResults->setAppliedTemplateFile($templatePath);
                $this->logger->info(sprintf(
                    'Match found: template "%s" extracted %d key(s)',
                    basename($templatePath),
                    $this->parseResults->countResults()
                ));
            }
        }

        if ($this->parseResults->getAppliedTemplateFile() === null) {
            $this->logger->info('No template matched the provided text');
        }

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
