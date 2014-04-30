<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link https://github.com/phergie/phergie-irc-plugin-react-scaffold for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Plugin\React\Scaffold
 */

namespace Phergie\Irc\Plugin\React\Scaffold;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class for a command to generate scaffolding for a new Phergie plugin.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\Scaffold
 */
class ScaffoldCommand extends Command
{
    /**
     * Last error message encountered
     *
     * @var string
     */
    protected $error;

    /**
     * Command parameters
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * Path to directory containing template files
     *
     * @var string
     */
    protected $templatePath;

    public function __construct()
    {
        parent::__construct();
        $this->templatePath = __DIR__ . '/../templates';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getInput($output);
        if (!($this->createDirectories($output)
            && $this->generatePluginFile($output)
            && $this->generateTestFile($output)
            && $this->generatePHPUnitFile($output)
            && $this->generateTravisFile($output)
            && $this->generateLicenseFile($output)
            && $this->generateReadmeFile($output)
            && $this->generateComposerFile($output)
            && $this->generateGitIgnoreFile($output)
            && $this->runComposerInstall($output))) {
            $output->writeln('<error>' . $this->error . '</error>');
        }
    }

    protected function getInput(OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        $this->parameters['short_name'] = $dialog->ask($output, 'Short plugin name: ');

        $this->parameters['purpose'] = $dialog->ask($output, 'Plugin purpose: ');

        $default = 'phergie/phergie-irc-plugin-react-' . strtolower($this->parameters['short_name']);
        $this->parameters['composer_name'] = $dialog->ask($output, 'composer.json name attribute (default ' . $default . '): ', $default);

        $default = 'https://github.com/' . $this->parameters['composer_name'];
        $this->parameters['github_url'] = $dialog->ask($output, 'GitHub URL (default ' . $default . '): ', $default);
        $this->parameters['github_repo'] = substr(strrchr($this->parameters['github_url'], '/'), 1);

        $default = 'Phergie\\Irc\\Plugin\\React\\' . ucfirst($this->parameters['short_name']);
        $this->parameters['namespace'] = $namespace = $dialog->ask($output, 'PHP namespace (default ' . $default . '): ', $default);
        $this->parameters['plugin_test_dir'] = 'tests/' . str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
        $this->parameters['testsuite_dir'] = './' . substr($namespace, 0, strpos($namespace, '\\')) . '/';
        $this->parameters['composer_namespace'] = addslashes($namespace);

        $this->parameters['command_plugin'] = $command = $dialog->ask($output, 'Command plugin (y/n, default n): ', 'n') === 'y';
        $this->parameters['event_class_full'] = 'Phergie\\Irc\\' . ($command ? 'Plugin\React\Command\CommandEvent' : 'Event\EventInterface');
        $this->parameters['event_class_short'] = ltrim(strrchr($this->parameters['event_class_full'], '\\'), '\\');
        $this->parameters['handler_method'] = 'handle' . ($command ? 'Command' : 'Event');

        $default = '~/bin/composer.phar';
        $this->parameters['composer'] = $dialog->ask($output, 'Command to run composer (default ' . $default . '): ', $default);

        return true;
    }

    protected function createDirectories(OutputInterface $output)
    {
        $dirs = array(
            '',
            '/src',
            '/' . $this->parameters['plugin_test_dir'],
        );

        $repo = $this->parameters['github_repo'];
        foreach ($dirs as $dir) {
            $dir = $repo . $dir;
            if (!file_exists($dir)) {
                if (mkdir($dir, 0777, true)) {
                    $output->writeln('<info>Created plugin directory ' . $dir . '</info>');
                } else {
                    $this->error = 'Unable to create plugin directory' . $dir;
                    return false;
                }
            } else {
                $output->writeln('<comment>Plugin directory ' . $dir . ' already exists, skipping creation</comment>');
            }
        }

        if (file_exists($repo . '/.git')) {
            $output->writeln('<comment>Plugin directory ' . $repo . ' is already a git repository, skipping initialization</comment>');
            return true;
        }

        chdir($repo);
        $init = new Process('git init');
        $init->run();
        if (!$init->isSuccessful()) {
            $this->error = 'Failed to initialize git repository';
            return false;
        }
        chdir('..');
        $output->writeln('<info>Initialized git repository in plugin directory ' . $repo . '</info>');
        return true;
    }

    protected function generatePluginFile(OutputInterface $output)
    {
        return $this->generateFile(
            'Plugin.php.twig',
            'src/Plugin.php',
            'plugin',
            $output
        );
    }

    protected function generateTestFile(OutputInterface $output)
    {
        return $this->generateFile(
            'PluginTest.php.twig',
            $this->parameters['plugin_test_dir'] . '/PluginTest.php',
            'test',
            $output
        );
    }

    protected function generatePHPUnitFile(OutputInterface $output)
    {
        return $this->generateFile(
            'phpunit.xml.twig',
            'tests/phpunit.xml',
            'PHPUnit',
            $output
        );
    }

    protected function generateTravisFile(OutputInterface $output)
    {
        return $this->generateFile(
            '.travis.yml.twig',
            '.travis.yml',
            'Travis CI',
            $output
        );
    }

    protected function generateLicenseFile(OutputInterface $output)
    {
        return $this->generateFile(
            'LICENSE.twig',
            'LICENSE',
            'license',
            $output
        );
    }

    protected function generateReadmeFile(OutputInterface $output)
    {
        return $this->generateFile(
            'README.md.twig',
            'README.md',
            'README',
            $output
        );
    }

    protected function generateComposerFile(OutputInterface $output)
    {
        return $this->generateFile(
            'composer.json.twig',
            'composer.json',
            'composer',
            $output
        );
    }

    protected function generateGitIgnoreFile(OutputInterface $output)
    {
        return $this->generateFile(
            '.gitignore.twig',
            '.gitignore',
            '.gitignore',
            $output
        );
    }

    protected function runComposerInstall(OutputInterface $output)
    {
        $repo = $this->parameters['github_repo'];
        if (file_exists($repo . '/vendor')) {
            $output->writeln('<comment>Plugin directory ' . $repo . ' already contains composer vendor directory, skipping dependency installation');
        }

        $output->writeln('<info>Installing composer dependencies in plugin directory ' . $repo . '</info>');
        chdir($repo);
        $install = new Process(
            'php ' . $this->parameters['composer'] . ' install -o',
            null,
            null,
            null,
            120
        );
        $install->run(function($type, $buffer) {
            $dst = $type === Process::ERR ? STDERR : STDOUT;
            fwrite($dst, $buffer);
        });
        if (!$install->isSuccessful()) {
            $this->error = 'Failed to install composer dependencies: ' . $install->getErrorOutput();
            return false;
        }
        chdir('..');
        $output->writeln('<info>Done</info>');
        return true;
    }

    protected function configure()
    {
        $this->setName('scaffold');
    }

    /**
     * Returns a configured Twig object.
     *
     * @return \Twig_Environment
     */
    protected function getTwig()
    {
        $loader = new \Twig_Loader_Filesystem($this->templatePath);
        $twig = new \Twig_Environment($loader, array('autoescape' => false));
        return $twig;
    }

    /**
     * Generates a file using Twig.
     *
     * @param string $template Name of the template file
     * @param string $path File path to receive the generated result
     * @param string $description Description of the file being generated
     * @param OutputInterface $output
     */
    protected function generateFile($template, $path, $description, OutputInterface $output)
    {
        $path = $this->parameters['github_repo'] . '/' . $path;
        if (file_exists($path)) {
            $output->writeln('<comment>Found existing ' . $description . ' file at ' . $path . ', not overwriting</comment>');
            return true;
        }

        $twig = $this->getTwig();
        $template = $twig->loadTemplate($template);
        $contents = $template->render($this->parameters);
        if (!file_put_contents($path, $contents)) {
            $this->error = 'Unable to create ' . $description . ' file';
            return false;
        }

        $output->writeln('<info>Created ' . $description . ' file ' . $path . '</info>');
        return true;
    }
}
