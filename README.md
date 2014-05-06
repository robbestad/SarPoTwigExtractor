#PO Twig Extractor
_BETA!_

###Assumptions (!important): 

* This module is placed in your /module folder

* You use translate('Word to translate') in your .twig files

* You have at least one set of language files in your module/XXX/language folder

###TODO:

* (Untested) Putting the module in the /vendor folder (Why? So I can upload it on packagist and use it in my composer.json files)

* Creating a better procedure for launching the extractor 

#About

I needed a way to extract all my translatable strings from my .twig files.
None of the existing methods worked for me, but this one does.

Here's how you run it:

From the console, go to the root of your project:

execute _php Module.php XXX_ (where XXX is the name of your module, for instance Application)

######This is what it does:
It traverses your module/Application/view folder for .twig files and fetches
all the strings within translate('xxx'). It also traverses all your language files
in your module/Application/language folder and compiles a list of already translated 
files. 

The strings that doesn't exist in your language files will be added. You can then open the 
language file in Poedit, and you're ready to go. 

### License

Public domain

### Author

Sven Anders Robbestad, may 2014
