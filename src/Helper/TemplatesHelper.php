<?php

namespace aymanrb\UnstructuredTextParser\Helper;

use aymanrb\UnstructuredTextParser\Exception\InvalidTemplatesDirectoryException;

class TemplatesHelper
{
    /** @var \FilesystemIterator; Iterable Directory */
    private $filesystemIterator;


    public function __construct(string $templatesDir)
    {
        $this->filesystemIterator = $this->createTemplatesDirIterator($templatesDir);
    }

    public function getTemplates(string $text, bool $findMatchingTemplate = false): array
    {
        if ($findMatchingTemplate) {
            return $this->findTemplate($text);
        }

        return $this->getAllValidTemplates();
    }

    private function createTemplatesDirIterator(string $iterableFilesystemPath): \FilesystemIterator
    {
        if (empty($iterableFilesystemPath) || !is_dir($iterableFilesystemPath)) {
            throw new InvalidTemplatesDirectoryException(
                'Invalid templates directory provided'
            );
        }

        return new \FilesystemIterator(rtrim($iterableFilesystemPath, '/'));
    }

    private function findTemplate(string $text): array
    {
        $matchedTemplate = [];
        $maxMatch = -1;

        foreach ($this->filesystemIterator as $fileInfo) {
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
        foreach ($this->filesystemIterator as $fileInfo) {
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
