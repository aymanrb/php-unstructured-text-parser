<?php

namespace aymanrb\UnstructuredTextParser\Helper;

use aymanrb\UnstructuredTextParser\Exception\InvalidTemplatesDirectoryException;

class TemplatesHelper
{
    /** @var \DirectoryIterator; Iterable Directory */
    private $directoryIterator;


    public function __construct(string $templatesDir)
    {
        $this->directoryIterator = $this->createTemplatesDirIterator($templatesDir);
    }

    public function getTemplates(string $text, bool $findMatchingTemplate): array
    {
        if ($findMatchingTemplate) {
            return $this->findTemplate($text);
        }

        return $this->getAllValidTemplates();
    }

    private function createTemplatesDirIterator(string $iterableDirectoryPath): \DirectoryIterator
    {
        if (empty($iterableDirectoryPath) || !is_dir($iterableDirectoryPath)) {
            throw new InvalidTemplatesDirectoryException(
                'Invalid templates directory provided'
            );
        }

        return new \DirectoryIterator(rtrim($iterableDirectoryPath, '/'));
    }

    private function findTemplate(string $text): array
    {
        $matchedTemplate = [];
        $maxMatch = -1;

        foreach ($this->directoryIterator as $fileInfo) {
            $templateContent = file_get_contents($fileInfo->getPathname());

            //Compare template against text to decide on similarity percentage
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

        return $templates;
    }

    private function prepareTemplate(string $templateText): string
    {
        $patterns = [
            '/\\\{%(.*)%\\\}/U', // 1 Replace all {%Var%}...
            '/\s+/',             // 2 Replace all white-spaces...
        ];

        $replacements = [
            '(?<$1>.*)',         // 1 ...with (?<Var>.*)
            ' ',                 // 2 ...with a single space
        ];

        $templateText = preg_replace($patterns, $replacements, preg_quote($templateText, '/'));

        return trim($templateText);
    }
}