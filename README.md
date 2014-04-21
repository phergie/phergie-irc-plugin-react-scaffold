# phergie/phergie-irc-plugin-react-scaffold

Utility used to generate files for [Phergie](http://github.com/phergie/phergie-irc-bot-react/) plugins.

## Install

Simply clone or download this repository and run the install command of [composer](http://getcomposer.org) from the repository root.

## Usage

Run `bin/phergie-scaffold` from any directory. Within that directory, a new
directory will be created containing files for a new plugin based on input
gathered by the program when it is run.

```
$ ./phergie-irc-plugin-react-scaffold/bin/phergie-scaffold 
Short plugin name: Quit
Plugin purpose: providing a command to instruct the bot to terminate a connection
composer.json name attribute (default phergie/phergie-irc-plugin-react-quit): 
GitHub URL (default https://github.com/phergie/phergie-irc-plugin-react-quit): 
PHP namespace (default Phergie\Irc\Plugin\React\Quit): 
Command to run composer (default ~/bin/composer.phar): 
Created plugin directory phergie-irc-plugin-react-quit
Created plugin directory phergie-irc-plugin-react-quit/src
Created plugin directory phergie-irc-plugin-react-quit/tests/Phergie/Irc/Plugin/React/Quit
Initialized git repository in plugin directory phergie-irc-plugin-react-quit
Created plugin file phergie-irc-plugin-react-quit/src/Plugin.php
Created test file phergie-irc-plugin-react-quit/tests/Phergie/Irc/Plugin/React/Quit/PluginTest.php
Created PHPUnit file phergie-irc-plugin-react-quit/tests/phpunit.xml
Created Travis CI file phergie-irc-plugin-react-quit/.travis.yml
Created license file phergie-irc-plugin-react-quit/LICENSE
Created README file phergie-irc-plugin-react-quit/README.md
Created composer file phergie-irc-plugin-react-quit/composer.json
Created .gitignore file phergie-irc-plugin-react-quit/.gitignore
Installing composer dependencies in plugin directory phergie-irc-plugin-react-quit
[snip]
Done
```

## License

Released under the BSD License. See `LICENSE`.
