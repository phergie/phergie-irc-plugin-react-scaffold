# phergie/phergie-irc-plugin-react-scaffold

Utility used to generate files for [Phergie](http://github.com/phergie/phergie-irc-bot-react/) plugins.

## Install

Simply clone or download this repository and run the install command of [composer](http://getcomposer.org) from the repository root.

## Usage

Run `bin/phergie-scaffold` from any directory. Within that directory, a new
directory will be created containing files for a new plugin based on input
gathered by the program when it is run.

```
$ ./bin/phergie-scaffold
Short plugin name: Test
Plugin purpose: testing the scaffolding tool
Plugin URL: https://github.com/phergie/phergie-irc-plugin-react-test
Author name (default Phergie Development Team):
Author e-mail address (default team@phergie.org):
Author URL (default http://phergie.org):
License name (default Simplified BSD License):
License URL (default http://phergie.org/license):
Composer license value (default BSD-2-Clause):
Copyright years (default 2015): 2008-2015
composer.json name attribute (default phergie/phergie-irc-plugin-react-test):
Repo URL (default https://github.com/phergie/phergie-irc-plugin-react-test):
Issues URL: https://github.com/phergie/phergie-irc-plugin-react-test/issues
PHP namespace (default Phergie\Irc\Plugin\React\Test):
Command plugin (y/n, default n):
Command to run composer (default php ~/bin/composer.phar):
Created plugin directory phergie-irc-plugin-react-test
Created plugin directory phergie-irc-plugin-react-test/src
Created plugin directory phergie-irc-plugin-react-test/tests/Phergie/Irc/Plugin/React/Test
Initialized git repository in plugin directory phergie-irc-plugin-react-test
Created plugin file phergie-irc-plugin-react-test/src/Plugin.php
Created test file phergie-irc-plugin-react-test/tests/Phergie/Irc/Plugin/React/Test/PluginTest.php
Created PHPUnit file phergie-irc-plugin-react-test/tests/phpunit.xml
Created Travis CI file phergie-irc-plugin-react-test/.travis.yml
Created license file phergie-irc-plugin-react-test/LICENSE
Created README file phergie-irc-plugin-react-test/README.md
Created composer file phergie-irc-plugin-react-test/composer.json
Created .gitignore file phergie-irc-plugin-react-test/.gitignore
Installing composer dependencies in plugin directory phergie-irc-plugin-react-test
Loading composer repositories with package information
Installing dependencies (including require-dev)
[snip]
Done
```

## Configuration

An optional configuration file can be used to control the default setting
values that are used. The path to this configuration file is assumed by be
`~/.phergie-scaffold` by default, but can be changed by passing a different
path as the first parameter to `bin/phergie-scaffold`.

This file uses a simple line-based X=Y format. Here are the supported settings
for which values may be specified in this file:

| Name | Description | Default Value |
| ---- | ----------- | ------------- |
| author_email | E-mail address of the plugin author | `team@phergie.org` |
| author_name | Name of the plugin author | `Phergie Development Team` |
| author_url | URL of the web site for the plugin author | `http://phergie.org` |
| base_composer_name | Prefix for [composer `name` attribute](https://getcomposer.org/doc/04-schema.md#name) to which the specified value for "Short plugin name" will be appended | `phergie/phergie-irc-plugin-react-` |
| base_namespace | Base namespace to which the specified value for "Short plugin name" will be appended | `Phergie\\Irc\\Plugin\\React\\` |
| base_repo_url | Base repo URL to which the full composer package name will be appended | `https://github.com/` |
| command_event_class | Event class or interface used for command events | `Phergie\\Irc\\Plugin\\React\\Command\\CommandEvent` |
| command_handler_method | Name of the handler method stub in the plugin class for command events | handleCommand` |
| command_plugin | y/n flag indicating whether the plugin is a command plugin | `n` |
| composer_command | Command used to invoke Composer | `php ~/bin/composer.phar` |
| copyright_years | Range of copyright years for the plugin source code | String containing the current year |
| license_name | Name of the license for the plugin | `Simplified BSD License` |
| license_url | URL of the license for the plugin | `http://phergie.org/license` |
| license_value | [Composer value of the license](https://getcomposer.org/doc/04-schema.md#license) for the plugin | `BSD-2-Clause` |
| standard_event_class | Event class or interface used for non-command events | `Phergie\\Irc\\Event\\EventInterface` |
| standard_handler_method | Name of the handler method stub in the plugin class for non-command events | `handleEvent` |

Here's an example configuration file:

```
author_email=me@matthewturland.com
author_name=Matthew Turland
author_url=http://matthewturland.com
copyright_years=2008-2015
license_name=MIT License
license_url=http://opensource.org/licenses/MIT
license_value=MIT
```

## License

Released under the BSD License. See `LICENSE`.
