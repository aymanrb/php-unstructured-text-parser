<?php

namespace aymanrb\UnstructuredTextParser;

use DirectoryIterator;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class TextParser
{
    use LoggerAwareTrait;

    /** @var DirectoryIterator; Templates Directory Iterator */
    protected $directoryIterator;

    /**
     * @param string $templatesDir ; The path to the template files directory
     * @param LoggerInterface $logger ; A logger instance implementing the PSR-3 Logger interface
     */
    public function __construct($templatesDir, LoggerInterface $logger = null)
    {
        if (empty($logger)) {
            $logger = new NullLogger();
        }

        $this->setLogger($logger);
        $this->createTemplatesDirIterator($templatesDir);
    }

    /**
     * The call for action method, this is the parse job initiator
     *
     * @param string $text ; The text provided by the user for parsing
     * @param boolean $findMatchingTemplate ; A boolean to enable the similarity match against templates before parsing (slower)
     * @return array The matched data array or null on unmatched text
     *
     */
    public function parseText($text, $findMatchingTemplate = false)
    {
        $this->logger->info(sprintf('Parsing: %s', $text));

        $text = $this->prepareText($text);
        $matchedTemplates = $this->getTemplates($text, $findMatchingTemplate);

        foreach ($matchedTemplates as $templatePath => $templateContent) {
            $this->logger->debug(sprintf('Parsing against template: %s', $templatePath));

            $templatePattern = $this->prepareTemplate($templateContent);
            $extractedData = $this->extractData($text, $templatePattern);

            if ($extractedData) {
                $this->logger->info(sprintf('Data extracted: %s', json_encode($extractedData)));

                return $extractedData;
            }
        }

        return null;
    }

    /**
     * Returns array of available template patterns or performs a similarity match (slower) to return best match template
     *
     * @param string $text ; The text provided by the user for parsing
     * @param boolean $findMatchingTemplate ; A boolean to enable the similarity match against templates before parsing
     * @return array
     */
    protected function getTemplates($text, $findMatchingTemplate)
    {
        if ($findMatchingTemplate) {
            return $this->findTemplate($text);
        }

        $templates = [];
        foreach ($this->directoryIterator as $fileInfo) {
            if (!is_file($fileInfo->getPathname())) {
                continue;
            }

            $templates[$fileInfo->getPathname()] = file_get_contents($fileInfo->getPathname());
        }

        return $templates;
    }

    /**
     * Sets the class property $templatesDirectoryPath
     *
     * @param string $templatesDir ; The path to the template files directory
     * @throws \Exception
     */

    protected function createTemplatesDirIterator($templatesDir)
    {
        if (empty($templatesDir) || !is_dir($templatesDir)) {
            throw new \Exception('Invalid templates directory provided');
        }

        $this->directoryIterator = new DirectoryIterator(rtrim($templatesDir, '/'));
    }

    /**
     * Prepares the provided text for parsing by escaping known characters and removing exccess whitespaces
     *
     * @param string $txt ; The text provided by the user for parsing
     * @return string; The prepared clean text
     *
     */
    protected function prepareText($txt)
    {
        //Remove all multiple whitespaces and replace it with single space
        $txt = preg_replace('/\s+/', ' ', $txt);

        return trim($txt);
    }

    /**
     * Prepares the matched template text for parsing by escaping known characters and removing excess whitespaces
     *
     * @param string $templateTxt ; The matched template contents
     * @return string; The prepared clean template pattern
     *
     */
    protected function prepareTemplate($templateTxt)
    {
        $patterns = [
            '/\\\{%(.*)%\\\}/U', // 1 Replace all {%Var%}...
            '/\s+/',             // 2 Replace all white-spaces...
        ];

        $replacements = [
            '(?<$1>.*)',         // 1 ...with (?<Var>.*)
            ' ',                 // 2 ...with a single space
        ];

        $templateTxt = preg_replace($patterns, $replacements, preg_quote($templateTxt, '/'));

        return trim($templateTxt);
    }

    /**
     * Extracts the named variables values from within the text based on the provided template
     *
     * @param string $text ; The prepared text provided by the user for parsing
     * @param string $template ; The template regex pattern from the matched template
     * @return array|bool; The matched data array or false on unmatched text
     *
     */
    protected function extractData($text, $template)
    {
        //Extract the text based on the provided template using REGEX
        preg_match('/' . $template . '/s', $text, $matches);

        //Extract only the named parameters from the matched regex array
        $keys = array_filter(array_keys($matches), 'is_string');
        $matches = array_intersect_key($matches, array_flip($keys));

        if (!empty($matches)) {
            return $this->cleanExtractedData($matches);
        }

        return false;
    }

    /**
     * Removes unwanted stuff from the data array like html tags and extra spaces
     *
     * @param mixed $matches ; Array with matched strings
     * @return array; The clean data array
     *
     */
    protected function cleanExtractedData($matches)
    {
        return array_map([$this, 'cleanElement'], $matches);
    }


    /**
     * A callback method to remove unwanted stuff from the extracted data element
     *
     * @param string $value ; The extracted text from the matched element
     * @return string; clean/stripped text
     *
     */
    protected function cleanElement($value)
    {
        return trim(strip_tags($value));
    }

    /**
     * Iterates through the templates directory to find the closest template pattern that matches the provided text
     *
     * @param string $text ; The text provided by the user for parsing
     * @return array; The matched template contents with its path as a key or empty array if none matched
     *
     */
    protected function findTemplate($text)
    {
        $matchedTemplate = [];
        $maxMatch = -1;

        foreach ($this->directoryIterator as $fileInfo) {
            $templateContent = file_get_contents($fileInfo->getPathname());

            similar_text($text, $templateContent,
                $matchPercentage); //Compare template against text to decide on similarity percentage

            if ($matchPercentage > $maxMatch) {
                $this->logger->debug(sprintf('Template "%s" is a best match for now', $fileInfo->getPathname()));

                $maxMatch = $matchPercentage;
                $matchedTemplate = [$fileInfo->getPathname() => $templateContent];
            }
        }

        return $matchedTemplate;
    }
}
