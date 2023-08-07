<?php

declare(strict_types = 1);

namespace aymanrb\UnstructuredTextParser\Helper;

use aymanrb\UnstructuredTextParser\Exception\InvalidTemplatesDirectoryException;

class TemplatesHelper
{
    private const REGEX_GENERIC_VARIABLE = '/\\\{%(.*)%\\\}/U'; //{%Var%}
    private const REGEX_VARIABLE_WITH_PATTERN = '/\\\{%([^%]+):(.*)%\\\}/U'; //{%Var:Pattern%}
    private const REGEX_PREPARED_VARIABLE_WITH_PATTERN = '/(\(\?[^)]*)./'; //(?<Var\>Pattern)
    private const REGEX_ORPHAN_BACKSLASH = '/(?<!\\\\)\\\\(?!\\\\)/';
    private const STR_SEARCH_TRIPLE_BACKSLASHES = '\\\\\\';

    private const REPLACE_GENERIC_VARIABLE = '(?<$1>.*)'; //(?<Var>.*)
    private const REPLACE_VARIABLE_WITH_PATTERN = '(?<$1>$2)'; //(?<Var>Pattern)

    private \FilesystemIterator $directoryIterator;

    public function __construct(string $templatesDir)
    {
        $this->directoryIterator = $this->createTemplatesDirIterator($templatesDir);
    }

    public function getTemplates(string $text, bool $findMatchingTemplate = false): array
    {
        if ($findMatchingTemplate) {
            return $this->findTemplate($text);
        }

        return $this->getAllValidTemplates();
    }

    private function createTemplatesDirIterator(string $iterableDirectoryPath): \FilesystemIterator
    {
        if (empty($iterableDirectoryPath) || !is_dir($iterableDirectoryPath)) {
            throw new InvalidTemplatesDirectoryException(
                'Invalid templates directory provided'
            );
        }

        return new \FilesystemIterator(rtrim($iterableDirectoryPath, '/'));
    }

    private function findTemplate(string $text): array
    {
        $matchedTemplate = [];
        $maxMatch = -1;

        foreach ($this->directoryIterator as $fileInfo) {
            $templateContent = file_get_contents($fileInfo->getPathname());

            // compare template against text to decide on similarity percentage
            similar_text($text, $templateContent, $matchPercentage);

            if ($matchPercentage > $maxMatch) {
                $maxMatch = $matchPercentage;
                $matchedTemplate = [$fileInfo->getPathname() => $this->prepareTemplate($templateContent)];
            }
        }

        return $matchedTemplate;
    }

    private function getAllValidTemplates(): array
    {
        $templates = [];
        foreach ($this->directoryIterator as $fileInfo) {
            if (!is_file($fileInfo->getPathname())) {
                continue;
            }

            $templateContent = file_get_contents($fileInfo->getPathname());
            $templates[$fileInfo->getPathname()] = $this->prepareTemplate($templateContent);
        }

        krsort($templates);

        return $templates;
    }

    private function prepareTemplate(string $templateText): string
    {
        $templateText = preg_quote($templateText, '/');

        $templateText =  preg_replace(
            self::REGEX_VARIABLE_WITH_PATTERN,
            self::REPLACE_VARIABLE_WITH_PATTERN,
            $templateText
        );

        $templateText = preg_replace_callback(
            self::REGEX_PREPARED_VARIABLE_WITH_PATTERN,
            function ($matches) {
                $variableWithPattern = preg_replace(self::REGEX_ORPHAN_BACKSLASH, '', $matches[0]);

                return str_replace(self::STR_SEARCH_TRIPLE_BACKSLASHES, '\\', $variableWithPattern);
            },
            $templateText
        );

        return preg_replace(
            self::REGEX_GENERIC_VARIABLE,
            self::REPLACE_GENERIC_VARIABLE,
            $templateText
        );
    }
}