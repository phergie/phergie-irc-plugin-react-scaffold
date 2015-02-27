<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link https://github.com/phergie/phergie-irc-plugin-react-scaffold for the canonical source repository
 * @copyright Copyright (c) 2008-2015 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Plugin\React\Scaffold
 */

namespace Phergie\Irc\Plugin\React\Scaffold;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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
    protected $parameters = [];

    /**
     * Path to directory containing template files
     *
     * @var string
     */
    protected $templatePath;

    /**
     * Associative array keyed by setting name of setting values that can be
     * overridden via a configuration file
     *
     * @var array
     */
    protected $defaultSettings;

    /**
     * Input interface in use
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * Output interface in use
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    public function __construct()
    {
        parent::__construct();
        $this->templatePath = __DIR__ . '/../templates';
        $this->defaultSettings = [
            'base_composer_name' => 'phergie/phergie-irc-plugin-react-',
            'base_namespace' => 'Phergie\\Irc\\Plugin\\React\\',
            'base_tests_namespace' => 'Phergie\\Irc\\Tests\\Plugin\\React\\',
            'standard_event_class' => 'Phergie\\Irc\\Event\\EventInterface',
            'command_event_class' => 'Phergie\\Irc\\Plugin\\React\\Command\\CommandEvent',
            'standard_handler_method' => 'handleEvent',
            'command_handler_method' => 'handleCommand',
            'composer_command' => 'php ~/bin/composer.phar',
            'command_plugin' => 'n',
            'base_repo_url' => 'https://github.com/',
            'copyright_years' => date('Y'),
            'author_name' => 'Phergie Development Team',
            'author_email' => 'team@phergie.org',
            'author_url' => 'http://phergie.org',
            'license_name' => 'New BSD License',
            'license_url' => 'http://phergie.org/license',
            'license_value' => 'BSD-2-Clause',
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->getInput();

        if (!($this->createDirectories()
            && $this->generatePluginFile()
            && $this->generateTestFile()
            && $this->generatePHPUnitFile()
            && $this->generateTravisFile()
            && $this->generateLicenseFile()
            && $this->generateReadmeFile()
            && $this->generateComposerFile()
            && $this->generateGitIgnoreFile()
            && $this->runComposerInstall())) {
            $output->writeln('<error>' . $this->error . '</error>');
        }
    }

    protected function getInput()
    {
        $defaultSettings = array_merge(
            $this->defaultSettings,
            $this->getDefaultSettingsFromFile()
        );

        $this->askForSetting($defaultSettings, 'short_name', 'Short plugin name');
        $this->askForSetting($defaultSettings, 'purpose', 'Plugin purpose');
        $this->askForSetting($defaultSettings, 'plugin_url', 'Plugin URL');
        $this->askForSetting($defaultSettings, 'author_name', 'Author name');
        $this->askForSetting($defaultSettings, 'author_email', 'Author e-mail address');
        $this->askForSetting($defaultSettings, 'author_url', 'Author URL');
        $this->askForSetting($defaultSettings, 'license_name', 'License name');
        $this->askForSetting($defaultSettings, 'license_url', 'License URL');
        $this->askForSetting($defaultSettings, 'license_value', 'Composer license value');
        $this->askForSetting($defaultSettings, 'copyright_years', 'Copyright years');

        $defaultSettings['composer_name'] = $defaultSettings['base_composer_name'] . strtolower($this->parameters['short_name']);
        $this->askForSetting($defaultSettings, 'composer_name', 'composer.json name attribute');

        $defaultSettings['repo_url'] = $defaultSettings['base_repo_url'] . $this->parameters['composer_name'];
        $this->askForSetting($defaultSettings, 'repo_url', 'Repo URL');
        $repoParts = explode('/', $this->parameters['repo_url']);
        $this->parameters['repo_name'] = array_pop($repoParts);
        $this->parameters['repo_owner'] = array_pop($repoParts);

        $defaultSettings['issues_url'] = $defaultSettings['repo_url'] . '/issues';
        $this->askForSetting($defaultSettings, 'issues_url', 'Issues URL');

        $defaultSettings['package_namespace'] = $defaultSettings['base_namespace'] . ucfirst($this->parameters['short_name']);
        $this->askForSetting($defaultSettings, 'package_namespace', 'PHP namespace');
        $packageNamespace = $this->parameters['package_namespace'];
        $this->parameters['vendor_namespace'] = substr($packageNamespace, 0, strpos($packageNamespace, '\\'));
        $this->parameters['composer_namespace'] = addslashes($packageNamespace);

        $defaultSettings['package_tests_namespace'] = $defaultSettings['base_tests_namespace'] . ucfirst($this->parameters['short_name']);
        $this->askForSetting($defaultSettings, 'package_tests_namespace', 'Tests namespace');
        $packageTestsNamespace = $this->parameters['package_tests_namespace'];
        $this->parameters['composer_tests_namespace'] = addslashes($packageTestsNamespace);

        $default = $defaultSettings['command_plugin'];
        $this->parameters['command_plugin'] = $command = $this->getDialogHelper()->askConfirmation($this->output,
            'Command plugin (y/n, default ' . $default . '): ', $default === 'y');
        $this->parameters['event_class_full'] = $defaultSettings[($command ? 'command' : 'standard') . '_event_class'];
        $this->parameters['event_class_short'] = ltrim(strrchr($this->parameters['event_class_full'], '\\'), '\\');
        $this->parameters['handler_method'] = $defaultSettings[($command ? 'command' : 'standard') . '_handler_method'];

        $this->askForSetting($defaultSettings, 'composer_command', 'Command to run composer');

        return true;
    }

    protected function getDialogHelper()
    {
        return $this->getHelperSet()->get('dialog');
    }

    protected function askForSetting($defaultSettings, $parameter, $label, $description = null)
    {
        $default = isset($defaultSettings[$parameter]) ? $defaultSettings[$parameter] : null;

        $prompt = $label;
        if ($description) {
            $prompt .= ' (' . $description;
            if ($default) {
                $prompt .= ', default ' . $default;
            }
            $prompt .= ')';
        } elseif ($default) {
            $prompt .= ' (default ' . $default . ')';
        }
        $prompt .= ': ';

        $this->parameters[$parameter] = $this->getDialogHelper()->ask($this->output, $prompt, $default);
    }

    protected function getDefaultSettingsFromFile()
    {
        $defaultSettings = [];
        $defaultsFile = $this->input->getArgument('defaults-file');

        if (file_exists($defaultsFile)) {
            $this->output->writeln('<info>Reading defaults from ' . $defaultsFile . '</info>');

            $lines = array_map('trim', file($defaultsFile));
            for ($no = 0; isset($lines[$no]); $no++) {
                $line = $lines[$no];
                if (strpos($line, '=') === false) {
                    $this->output->writeln('<comment>No = delimiter found on line ' . ($no + 1) . ' of ' . $defaultsFile . ': ' . $line . '</comment>');
                    continue;
                }
                list($key, $value) = array_map('trim', explode('=', $line, 2));
                $defaultSettings[$key] = $value;
            }
        }

        return $defaultSettings;
    }

    protected function createDirectories()
    {
        $dirs = [
            '',
            '/src',
            '/tests',
        ];

        $repo = $this->parameters['repo_name'];
        foreach ($dirs as $dir) {
            $dir = $repo . $dir;
            if (!file_exists($dir)) {
                if (mkdir($dir, 0777, true)) {
                    $this->output->writeln('<info>Created plugin directory ' . $dir . '</info>');
                } else {
                    $this->error = 'Unable to create plugin directory' . $dir;
                    return false;
                }
            } else {
                $this->output->writeln('<comment>Plugin directory ' . $dir . ' already exists, skipping creation</comment>');
            }
        }

        if (file_exists($repo . '/.git')) {
            $this->output->writeln('<comment>Plugin directory ' . $repo . ' is already a git repository, skipping initialization</comment>');
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
        $this->output->writeln('<info>Initialized git repository in plugin directory ' . $repo . '</info>');
        return true;
    }

    protected function generatePluginFile()
    {
        return $this->generateFile(
            'Plugin.php.twig',
            'src/Plugin.php',
            'plugin'
        );
    }

    protected function generateTestFile()
    {
        return $this->generateFile(
            'PluginTest.php.twig',
            'tests/PluginTest.php',
            'test'
        );
    }

    protected function generatePHPUnitFile()
    {
        return $this->generateFile(
            'phpunit.xml.twig',
            'phpunit.xml',
            'PHPUnit'
        );
    }

    protected function generateTravisFile()
    {
        return $this->generateFile(
            '.travis.yml.twig',
            '.travis.yml',
            'Travis CI'
        );
    }

    protected function generateLicenseFile()
    {
        return $this->generateFile(
            'LICENSE.twig',
            'LICENSE',
            'license'
        );
    }

    protected function generateReadmeFile()
    {
        return $this->generateFile(
            'README.md.twig',
            'README.md',
            'README'
        );
    }

    protected function generateComposerFile()
    {
        return $this->generateFile(
            'composer.json.twig',
            'composer.json',
            'composer'
        );
    }

    protected function generateGitIgnoreFile()
    {
        return $this->generateFile(
            '.gitignore.twig',
            '.gitignore',
            '.gitignore'
        );
    }

    protected function runComposerInstall()
    {
        $repo = $this->parameters['repo_name'];
        $this->output->writeln('<info>Installing composer dependencies in plugin directory ' . $repo . '</info>');
        chdir($repo);
        $install = new Process(
            $this->parameters['composer_command'] . ' install -o',
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
        $this->output->writeln('<info>Done</info>');
        return true;
    }

    protected function configure()
    {
        $homePath = (stripos(PHP_OS, 'win') !== false && stripos(PHP_OS, 'darwin') === false)
            ? $_SERVER['HOMEDRIVE'] . $_SERVER['HOME_PATH']
            : getenv('HOME');

        $this
            ->setName('scaffold')
            ->addArgument(
                'defaults-file',
                InputArgument::OPTIONAL,
                'path to a file containing default argument values to use',
                $homePath . DIRECTORY_SEPARATOR . '.phergie-scaffold'
            );
    }

    /**
     * Returns a configured Twig object.
     *
     * @return \Twig_Environment
     */
    protected function getTwig()
    {
        $loader = new \Twig_Loader_Filesystem($this->templatePath);
        $twig = new \Twig_Environment($loader, ['autoescape' => false]);
        return $twig;
    }

    /**
     * Generates a file using Twig.
     *
     * @param string $template Name of the template file
     * @param string $path File path to receive the generated result
     * @param string $description Description of the file being generated
     * @param OutputInterface $output
     * @return boolean
     */
    protected function generateFile($template, $path, $description)
    {
        $path = $this->parameters['repo_name'] . '/' . $path;
        if (file_exists($path)) {
            $this->output->writeln('<comment>Found existing ' . $description . ' file at ' . $path . ', not overwriting</comment>');
            return true;
        }

        $twig = $this->getTwig();
        $template = $twig->loadTemplate($template);
        $contents = $template->render($this->parameters);
        if (!file_put_contents($path, $contents)) {
            $this->error = 'Unable to create ' . $description . ' file';
            return false;
        }

        $this->output->writeln('<info>Created ' . $description . ' file ' . $path . '</info>');
        return true;
    }
}
