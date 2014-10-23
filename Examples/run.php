<?php
require_once('../src/TextParserClass.php');

try{
	$parser = new TextParser('templates');
	
	$parser->setLogFile('Logs/parser.log');

	$textFiles = new DirectoryIterator('test_txt_files');

	foreach($textFiles as $txtObj){
		if($txtObj->getExtension() == 'txt'){
			echo '<h1>' . $txtObj->getFilename() . '</h1>';
			$text = file_get_contents($txtObj->getPathname());
		
			echo "<pre>";
				print_r($parser->parseText($text));
			echo "</pre>";
		}
	}
}catch (Exception $e) {
    echo '<h1>Caught exception:</h1>' . $e->getMessage();
}
