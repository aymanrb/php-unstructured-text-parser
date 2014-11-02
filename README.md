Unstructured Text Parser [PHP]
===========================================
[![Build Status](https://travis-ci.org/aymanrb/php-unstructured-text-parser.svg?branch=master)](https://travis-ci.org/aymanrb/php-unstructured-text-parser)


About this Class
----------------------------------
This is a PHP Class to help extract text out of documents that are not structured in a processing friendly way. When you want to parse text out of form generated emails for example you can create a template matching the expected incoming mail format while specifying the variable text elements and leave the rest for the class to extract your preformatted variables out of the incoming mails' body text.

Useful when you want to parse data out of:
* Web Pages / HTML documents
* Emails generated from web forms
* Documents with definable templates / expressions


**Note:** When too many templates are added to the specified templates directory it slows the parsing process in a noticeable manner. This happens since the class runs over all the template files and compares it with the passed text to decide on the most suitable template for parsing.

Current Version
----------
1.0-beta

How it works
----------
1- Grab a single copy of the text you want to parse.

2- Replace every single varying text within it to a named variable in the form of ``{%VariableName%}``

3- Add the templates file into the templates directory you defined to the class with a txt extension ``fileName.txt``

4- Pass the text you wish to parse to the parse method of the class and let it do the magic for you.

Template Example
------------------------
If the text documents you want to parse looks like this:

```
Hi Guthuber,
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
Hi {%name_of_reciever%},
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
	'name_of_reciever' => 'Githuber',
    'sender_name' => 'Pet Cat',
    'sender_email' => 'email@example.com',
    'Comment' => 'Some text goes here'
)
```

*"Works perfectly with HTML tags and anything else you may wish"*
