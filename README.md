Unstructured Text Parser [PHP]
===========================================
[![Build Status](https://travis-ci.org/aymanrb/php-unstructured-text-parser.svg?branch=master)](https://travis-ci.org/aymanrb/php-unstructured-text-parser)
[![Coverage Status](https://coveralls.io/repos/github/aymanrb/php-unstructured-text-parser/badge.svg?branch=master)](https://coveralls.io/github/aymanrb/php-unstructured-text-parser?branch=master)
[![Latest Stable Version](https://poser.pugx.org/aymanrb/php-unstructured-text-parser/v/stable.svg)](https://packagist.org/packages/aymanrb/php-unstructured-text-parser)
[![Latest Unstable Version](https://poser.pugx.org/aymanrb/php-unstructured-text-parser/v/unstable.svg)](https://packagist.org/packages/aymanrb/php-unstructured-text-parser)
[![Total Downloads](https://poser.pugx.org/aymanrb/php-unstructured-text-parser/downloads)](https://packagist.org/packages/aymanrb/php-unstructured-text-parser)
[![License](https://poser.pugx.org/aymanrb/php-unstructured-text-parser/license.svg)](https://packagist.org/packages/aymanrb/php-unstructured-text-parser)



About Unstructured Text Parser
----------------------------------
This is a small PHP library to help extract text out of documents that are not structured in a processing friendly format. 
When you want to parse text out of form generated emails for example you can create a template matching the expected incoming mail format 
while specifying the variable text elements and leave the rest for the class to extract your pre-formatted variables out of the incoming mails' body text.

Useful when you want to parse data out of:
* Emails generated from web forms
* Documents with definable templates / expressions

Installation
----------
PHP Unstructured Text Parser is available on [Packagist](https://packagist.org/packages/aymanrb/php-unstructured-text-parser) (using semantic versioning), and installation via [Composer](https://getcomposer.org) is recommended. 
Add the following line to your `composer.json` file:

```json
"aymanrb/php-unstructured-text-parser": "~2.0"
```

or run

```sh
composer require aymanrb/php-unstructured-text-parser
```


[Usage example](https://github.com/aymanrb/php-unstructured-text-parser/blob/master/examples/run.php)
----------
```php
<?php
include_once __DIR__ . '/../vendor/autoload.php';

$parser = new aymanrb\UnstructuredTextParser\TextParser('/path/to/templatesDirectory');

$textToParse = 'Text to be parsed fetched from a file, mail, web service, or even added directly to the a string variable like this';

//performs brute force parsing against all available templates, returns first match successful parsing
$parseResults = $parser->parseText($textToParse);
print_r($parseResults->getParsedRawData());

//slower, performs a similarity check on available templates to select the most matching template before parsing
print_r(
    $parser
        ->parseText($textToParse, true)
        ->getParsedRawData()
);
```

Parsing Procedure
----------
1- Grab a single copy of the text you want to parse.

2- Replace every single varying text within it to a named variable in the form of ``{%VariableName%}``

3- Add the templates file into the templates directory (defined in parsing code) with a txt extension ``fileName.txt``

4- Pass the text you wish to parse to the parse method of the class and let it do the magic for you.

Template Example
------------------------
If the text documents you want to parse looks like this:

```
Hi GitHub-er,
If you wish to parse message coming from a website that states info like:
Name: Pet Cat
E-Mail: email@example.com
Comment: Some text goes here

Thank You,
Best Regards
Admin
```

Your Template file (``example_template.txt``) could be something like:

```
Hi {%nameOfRecipient%},
If you wish to parse message coming from a website that states info like:
Name: {%senderName%}
E-Mail: {%senderEmail%}
Comment: {%comment%}

Thank You,
Best Regards
Admin
```

The output of a successful parsing job would be:

```
Array(
    'nameOfRecipient' => 'GitHub-er',
    'senderName' => 'Pet Cat',
    'senderEmail' => 'email@example.com',
    'comment' => 'Some text goes here'
)
```

Upgrading from v1.x to v2.x
------------------------
Version 2.0 is more or less a refactored copy of version 1.x of the library and provides the exact same functionality.
There is just one slight difference in the results returned. It's now a parsed data object instead of an array.
To get the results as an array like it used to be in v1.x simply call "*getParsedRawData()*" on the returned object.

```php
<?php
//ParseText used to return array in 1.x
$extractedArray = $parser->parseText($textToParse);

//In 2.x you need to do the following if you want an array
$extractedArray = $parser->parseText($textToParse)->getParsedRawData();
```