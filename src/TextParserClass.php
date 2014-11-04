<?php
/**
 * TextParser Class
 *
 * A class to ease the process of extracting unstructured data from within
 * text files based on a predefined template using PHP regular expressions.
 *
 * @author     Ayman R. Bedair <http://www.AymanRB.com>
 * @version    1.0.2-beta
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 */
class TextParser {
    /**
     * Path to the templates directory on the server without prevailing slashes
     * @var String
     * @access private
     * @see setTemplatesDir()
     */

    private $templatesDirectoryPath = NULL;

    /**
     * Path to the log file
     * @var String
     * @access private
     * @see setLogFile()
     */

    private $logFile = NULL;

    /**
     * A boolean value to enable or disable the logging of the parsing process
     * @var Boolean
     * @access private
     * @see enableLogging(), getLogStatus()
     */

    private $isLoggingEnabled = false;

    /**
     * A global string variable to hold up all the log data for a one shot save at the end
     * @var String
     * @access private
     * @see enableLogging(), getLogStatus()
     */

    private $logData = NULL;

    /**
     * Constructor of the class
     *
     * @param String templatesDir; The path to the template files directory
     * @return void
     *
     */

    public function __construct($templatesDir) {
        $this->setTemplatesDir($templatesDir);
    }

    /**
     * Sets the private variable of the class $templatesDirectoryPath
     *
     * @param String templatesDir; The path to the template files directory
     * @return void
     * @access public
     *
     */

    public function setTemplatesDir($templatesDir) {
        if (empty($templatesDir) || !is_dir($templatesDir)) {
            $this->returnError('Invalid templates directory provided');
        }
        $this->templatesDirectoryPath = $templatesDir;
    }

    /**
     * The call for action method, this is the parse job initiator
     *
     * @param string $text; The text provided by the user for parsing
     * @return mixed				 The matched data array or null on unmatched text
     * @access public
     *
     */

    public function parseText($text) {
        $this->logRow('Parsing: ' . $text);
        //Prepare the text for parsing
        $text            = $this->prepareText($text);
        //Find the most appropriate template for this text format and preapre it for parsing
        $matchedTemplate = $this->findTemplate($text);
        $matchedTemplate = $this->prepareTemplate($matchedTemplate);
        $extractedData   = $this->extractData($text, $matchedTemplate);
        if ($extractedData) {
            $this->logRow('Got this: ' . json_encode($extractedData));
        } else {
            $this->logRow('Got nothing');
        }
        $this->savelog();
        return $extractedData;
    }

    /**
     * Prepares the provided text for parsing by escaping known characters and removing exccess whitespaces
     *
     * @param string txt; The text provided by the user for parsing
     * @return string; The prepared clean text
     * @access private
     *
     */

    private function prepareText($txt) {
        $txt = preg_replace('/\s+/', ' ', $txt); //Remove all multiple whitespaces and replace it with single space
        return trim($txt);
    }

    /**
     * Prepares the matched template text for parsing by escaping known characters and removing exccess whitespaces
     *
     * @param string $templateTxt; The matched template contents
     * @return string; The prepared clean template pattern
     * @access private
     *
     */

    private function prepareTemplate($templateTxt) {
        $templateTxt = preg_replace('/{%(.*)%}/U', '(?<$1>.*)', $templateTxt); //Replace all {Var} notation to (?<Var>.*) for regex pattern
        $templateTxt = str_replace(array('|',"'"), array('\|',"\'"), $templateTxt); //Escape buggy characters
        $templateTxt = preg_replace('/\s+/', ' ', $templateTxt); //Remove all multiple whitespaces and replace it with single space
        return trim($templateTxt);
    }

    /**
     * Extracts the named variables values from within the text based on the provided template
     *
     * @param string $text; 			The prepared text provided by the user for parsing
     * @param string $template;	The template regex pattern from the matched template
     * @return mixed;						The matched data array or false on unmatched text
     * @access private
     *
     */

    private function extractData($text, $template) {
        //Extract the text based on the provided template using REGEX
        preg_match("|" . $template . "|s", $text, $matches);
        //Extract only the named parameters from the matched regex array
        $keys    = array_filter(array_keys($matches), 'is_string');
        $matches = array_intersect_key($matches, array_flip($keys));
        if (empty($matches)) {
            return false;
        }
        return $this->cleanExtractedData($matches);
    }

    /**
     * Removes unwanted stuff from the data array like html tags and extra spaces
     *
     * @param mixed $matches; 		Array with matched strings
     * @return mixed;						The cleaned data array
     * @access private
     *
     */

    private function cleanExtractedData($matches) {
    		return array_map(array($this, 'cleanElement'), $matches);
    }
    
    
    /**
     * A callback method to remove unwanted stuff from the extracted data element
     *
     * @param string $value; 		The extracted text from the matched element
     * @return string;					Clean text
     * @access private
     * @see cleanExtractedData()
     *
     */
    private function cleanElement($value) {
    		return trim(strip_tags($value));
    }

    /**
     * Iterates through the templates directory to find the closest template pattern that matches the provided text
     *
     * @param string $text 		The text provided by the user for parsing
     * @return string					The matched template contents or false if no templates were found
     * @access private
     *
     */

    private function findTemplate($text) {
        $matchedTemplate = false;
        $maxMatch        = -1;
        $matchedFile     = NULL;
        $directory       = new DirectoryIterator($this->templatesDirectoryPath);
        
        foreach ($directory as $fileInfo) {
            if ($fileInfo->getExtension() == 'txt') { //make sure it's a text file
                $data  = file_get_contents($fileInfo->getPathname());
                $match = similar_text($text, $data, $matchPercent); //Compare template against text to decide on similarity percentage
                if ($matchPercent > $maxMatch) {
                    $maxMatch        = $matchPercent;
                    $matchedTemplate = $data;
                    $matchedFile     = $fileInfo->getPathname();
                } //End if current template match is higher
            } //End if current is a txt file
        } //End foreach loop over direcoty files
        
        $this->logRow('Matched Template File : ' . $matchedFile);
        return $matchedTemplate;
    }

    /**
     * Sets the private variable of the class $logsDirectoryPath. Calling this method will enable the logging by default
     *
     * @param String logDir; The path to the log files directory
     * @return void
     * @access public
     *
     */

    public function setLogFile($logFile) {
        if (empty($logFile) || !is_file($logFile)) {
            $this->returnError('Invalid logs filename / path provided');
        }
        if (!is_writable($logFile)) {
            $this->returnError('Logs file is not writable');
        }
        $this->enableLogging(); //Just to make sure enabling is not missed
        $this->logFile = $logFile;
    }

    /**
     * Returns the boolean value of the logging enabled flag
     *
     * @return Boolean; The logging enabled status
     * @access private
     *
     */

    private function getLogStatus() {
        return $this->isLoggingEnabled;
    }

    /**
     * Enables the logging of the parsing process
     *
     * @return void
     * @access private
     *
     */

    private function enableLogging() {
        $this->isLoggingEnabled = true;
    }

    /**
     * Adds data to the log of the current parsing session
     *
     * @return void
     * @access private
     *
     */

    private function logRow($logMessage) {
        $this->startNewLog();
        $this->logData .= $logMessage . "\n\n";
    }

    /**
     * Creates the first row of a new log session
     *
     * @return void
     * @access private
     *
     */

    private function startNewLog(){
        if (empty($this->logData)) {
            $this->logData = "==========================================================\n\n";
            $this->logRow("Started Parsing at: " . date('Y-m-d H:i:s'));
            $this->logRow("==========================================================");
        }
    }

    /**
     * Saves the recorded log to the file
     *
     * @return void
     * @access private
     *
     */

    private function saveLog() {
        if (!empty($this->logData) && $this->getLogStatus()) {
            file_put_contents($this->logFile, $this->logData, FILE_APPEND);
            $this->logData = NULL;
            return true;
        } else {
            return false;
        }
    }

    /**
     * A method to return the error based on the logging status
     *
     * @return void / Exception
     * @access private
     *
     */

    private function returnError($errorMessage) {
        if ($this->getLogStatus()) {
            $this->logRow($errorMessage);
            $this->saveLog();
            exit;
        } else {
            throw new Exception($errorMessage);
        }
    }
}
