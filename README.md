Unstructured Text Parser [PHP]
===========================================

What's this ?!
----------------------------------
This is a PHP Class to help extract text out of documents that are not structured in a processing friendly way. When you want to parse text out of form generated emails for example you can create a template matching the expected incoming mail format while specifying the variable text elements and leave the rest for the class to extract your preformatted variables out of the incoming mails' body text.

Useful when you want to parse data out of:
* Web Pages / HTML documents
* Emails generated from web forms
* Documents with definable templates / expressions


**Note:** When too many templates are added to the templates directory it slows the parsing process in a noticeable manner. This happens since the class runs over all the template files and compares it with the passed text to detect the most suitable template for parsing.

Version
----------
0.1-beta


How it works
----------
1- Grab a single copy of the text you want to parse.

2- Replace every single varying text within it to a name variable in the form of ``{%VariableName%}``

3- Add the templates file into the templates directory you defined to the class with a txt extension ``fileName.txt``

4- Pass the text you wish to parse to the parse method of and let it do the magic for you.

Template Example
------------------------
Lets assume the text documents you want to parse looks like this:

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

Your Template file (``example_template.txt``) should contain:

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

*"Works perfectl with HTML tags and anything else you may wish"*

