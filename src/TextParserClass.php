<?php
/**
 * TextParser Class
 *
 * A class to ease the process of extracting unstructured data from within
 * text files based on a predefined template using PHP regular expressions.
 * 
 * @author     Ayman R. Bedair <http://www.AymanRB.com>
 * @version    0.1-beta
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 */
 
class TextParser{
	
	/** 
	* Path to the templates directory on the server without prevailing slashes
	* @var String
	* @access private
	* @see setTemplatesDir()
	*/
	
	private $templatesDirectoryPath = NULL;
	
	/**
	* Constructor of the class
	*
	* @param String templatesDir; The path to the template files directory
	* @return void
	* 
	*/
	
	public function __construct($templatesDir){
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
	
	public function setTemplatesDir($templatesDir){
		
		if(empty($templatesDir) || !is_dir($templatesDir)){
			throw new Exception('Invalid templates directory provided');
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
	
	public function parseText($text){
		//Prepare the text for parsing
		$text = $this->prepareText($text);
		
		//Find the most appropriate template for this text format and preapre it for parsing
		$matchedTemplate = $this->findTemp($text);
		$matchedTemplate = $this->prepareTemplate($matchedTemplate);
		
		return $this->extractData($text, $matchedTemplate);
	}
	
	
	/** 
	* Prepares the provided text for parsing by escaping known characters and removing exccess whitespaces
	*
	* @param string txt; The text provided by the user for parsing
	* @return string; The prepared clean text
	* @access private
	*
	*/
	
	private function prepareText($txt){
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
	
	private function prepareTemplate($templateTxt){
		$templateTxt = preg_replace('/{%(.*)%}/U','(?<$1>.*)',$templateTxt); //Replace all {Var} notation to (?<Var>.*) for regex pattern
		$templateTxt = str_replace(array('|',"'"),array('\|',"\'"),$templateTxt); //Escape buggy characters 
		$templateTxt = preg_replace('/\s+/', ' ', $templateTxt); //Remove all multiple whitespaces and replace it with single space
		
		return trim($templateTxt);
	}
	
	/** 
	* Extracts the named variables values from within the text based on the provided template
	*
	* @param string $text; 			The prepared text provided by the user for parsing
	* @param string $template;	The template regex pattern from the matched template
	* @return mixed;						The matched data array or null on unmatched text
	* @access private
	*
	*/
	
	private function extractData($text, $template) {
		
		//Extract the text based on the provided template using REGEX
		preg_match("|".$template. "|s",$text,$matches);
		
		//Extract only the named parameters from the matched regex array
		$keys = array_filter(array_keys($matches),'is_string');
		$matches = array_intersect_key($matches,array_flip($keys));
		
		return $matches;
	}
	
	/** 
	* Iterates through the templates directory to find the closest template pattern that matches the provided text
	*
	* @param string $text 		The text provided by the user for parsing
	* @return string					The matched template contents or false if no templates were found
	* @access private
	*
	*/
	
  private function findTemp($text) {
  	
  	$matchedTemplate = false;
  	$maxMatch = -1;
  
  	$directory = new DirectoryIterator($this->templatesDirectoryPath);
  	
		foreach ($directory as $fileInfo) {

			if(!$fileInfo->isDot() && $fileInfo->getExtension() == 'txt'){
				
				$data = file_get_contents($fileInfo->getPathname());
				$match = similar_text($text, $data, $matchPercent);

				if($matchPercent > $maxMatch){
				  
					$maxMatch = $matchPercent;
					$matchedTemplate = $data;
					
				}//End if current template match is higher
				
			}//End if current is a txt file
			
		}//End foreach loop over direcoty files
		
		return $matchedTemplate;
	}

}
