<?php
require_once('../src/TextParserClass.php');

$parser = new TextParser('templates');

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
