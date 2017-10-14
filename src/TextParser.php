<?php

namespace aymanrb\UnstructuredTextParser;

use DirectoryIterator;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class TextParser
{
    /**
     * Path to the templates directory on the server without prevailing slashes
     * @var string
     */

    protected $templatesDirectoryPath = NULL;

    /**
     * Monolog Logger Object
     * @var Logger
     */
    protected $logger;

    /**
     * @param string $templatesDir; The path to the template files directory
     * @return boolean
     */

    public function __construct($templatesDir = null)
    {
        $this->logger = new Logger('text-parser');
        $this->logger->pushHandler(new StreamHandler('logs/text-parser.log', Logger::DEBUG));

        $this->setTemplatesDir($templatesDir);
    }

    /**
     * Sets the protected property of the class $templatesDirectoryPath
     *
     * @param String $templatesDir; The path to the template files directory
     * @return void
     * @throws \Exception
     */

    protected function setTemplatesDir($templatesDir)
    {
        if (empty($templatesDir) || !is_dir($templatesDir)) {
            throw new \Exception('Invalid templates directory provided');
        }

        $this->templatesDirectoryPath = $templatesDir;
    }

    /**
     * The call for action method, this is the parse job initiator
     *
     * @param string $text; The text provided by the user for parsing
     * @return array|bool The matched data array or null on unmatched text
     *
     */

    public function parseText($text)
    {
        $this->logger->info("==========================================================");
        $this->logger->info('Parsing: ' . $text);

        //Prepare the text for parsing
        $text = $this->prepareText($text);

        $matchedTemplate = $this->findTemplate($text);
        $matchedTemplate = $this->prepareTemplate($matchedTemplate);
        $extractedData = $this->extractData($text, $matchedTemplate);

        $this->logger->info('Data extracted: ' . json_encode($extractedData));

        return $extractedData;
    }

    /**
     * Prepares the provided text for parsing by escaping known characters and removing exccess whitespaces
     *
     * @param string $txt; The text provided by the user for parsing
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
     * Prepares the matched template text for parsing by escaping known characters and removing exccess whitespaces
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
     * @param string $text; The prepared text provided by the user for parsing
     * @param string $template; The template regex pattern from the matched template
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
     * @param mixed $matches; Array with matched strings
     * @return array; The clean data array
     *
     */

    protected function cleanExtractedData($matches)
    {
        return array_map(array($this, 'cleanElement'), $matches);
    }


    /**
     * A callback method to remove unwanted stuff from the extracted data element
     *
     * @param string $value ;        The extracted text from the matched element
     * @return string;                    Clean text
     *
     */
    protected function cleanElement($value)
    {
        return trim(strip_tags($value));
    }

    /**
     * Iterates through the templates directory to find the closest template pattern that matches the provided text
     *
     * @param string $text; The text provided by the user for parsing
     * @return string The matched template contents or false if no templates were found
     *
     */

    protected function findTemplate($text)
    {
        $matchedTemplate = false;
        $maxMatch = -1;
        $matchedFile = NULL;
        $directory = new DirectoryIterator($this->templatesDirectoryPath);

        foreach ($directory as $fileInfo) {
            if ($fileInfo->getExtension() == 'txt') {
                $data = file_get_contents($fileInfo->getPathname());

                similar_text($text, $data, $matchPercentage); //Compare template against text to decide on similarity percentage

                if ($matchPercentage > $maxMatch) {
                    $maxMatch = $matchPercentage;
                    $matchedTemplate = $data;
                    $matchedFile = $fileInfo->getPathname();
                }
            }
        }

        $this->logger->info(sprintf('Matched Template File %s', $matchedFile));
        return $matchedTemplate;
    }
}
