<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link https://github.com/phergie/phergie-irc-plugin-react-scaffold for the canonical source repository
 * @copyright Copyright (c) 2008-2015 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Plugin\React\Scaffold
 */

namespace Phergie\Irc\Plugin\React\Scaffold;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Custom application class to use the scaffold command by default.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\Scaffold
 * @see http://symfony.com/doc/current/components/console/single_command_tool.html
 */
class Application extends BaseApplication
{
    protected function getCommandName(InputInterface $input)
    {
        return 'scaffold';
    }

    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new ScaffoldCommand;
        return $defaultCommands;
    }

    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();
        return $inputDefinition;
    }
}
