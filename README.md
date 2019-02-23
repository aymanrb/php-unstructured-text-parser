Unstructured Text Parser [PHP]
===========================================
[![Build Status](https://travis-ci.org/aymanrb/php-unstructured-text-parser.svg?branch=master)](https://travis-ci.org/aymanrb/php-unstructured-text-parser)
[![Latest Stable Version](https://poser.pugx.org/aymanrb/php-unstructured-text-parser/v/stable.svg)](https://packagist.org/packages/aymanrb/php-unstructured-text-parser)
[![Latest Unstable Version](https://poser.pugx.org/aymanrb/php-unstructured-text-parser/v/unstable.svg)](https://packagist.org/packages/aymanrb/php-unstructured-text-parser)
[![License](https://poser.pugx.org/aymanrb/php-unstructured-text-parser/license.svg)](https://packagist.org/packages/aymanrb/php-unstructured-text-parser)


About this Class
----------------------------------
This is a PHP Class to help extract text out of documents that are not structured in a processing friendly way. When you want to parse text out of form generated emails for example you can create a template matching the expected incoming mail format while specifying the variable text elements and leave the rest for the class to extract your preformatted variables out of the incoming mails' body text.

Useful when you want to parse data out of:
* Emails generated from web forms
* Documents with definable templates / expressions

Installation
----------

#### 1- Using [composer](https://getcomposer.org/) simply run the following:

```shell
$ composer require aymanrb/php-unstructured-text-parser
```

#### 2- Clone / Copy the files from this repository to your local libs directory:

```shell
$ git clone https://github.com/aymanrb/php-unstructured-text-parser.git
```



[Usage example](https://github.com/aymanrb/php-unstructured-text-parser/blob/master/examples/run.php)
----------
```php
<?php
include_once __DIR__ . '/../vendor/autoload.php';

$parser = new aymanrb\UnstructuredTextParser\TextParser('/path/to/templatesDirectory');

$textToParse = 'Text to be parsed fetched from a file, mail, web service, or even added directly to the a string variable like this';

//performs brute force parsing against all available templates
print_r($parser->parseText($textToParse));

//slower, performs a similarity check on available templates before parsing
print_r($parser->parseText($textToParse, true)); 
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

Then your Template file (``example_template.txt``) should be:

```
Hi {%name_of_receiver%},
If you wish to parse message coming from a website that states info like:
Name: {%sender_name%}
E-Mail: {%sender_email%}
Comment: {%comment%}

Thank You,
Best Regards
Admin
```

The output of a successful parsing job would be:

```
Array(
    'name_of_receiver' => 'GitHub-er',
    'sender_name' => 'Pet Cat',
    'sender_email' => 'email@example.com',
    'Comment' => 'Some text goes here'
)
```
