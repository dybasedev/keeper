<?php
/**
 * ServerManageCommand.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Commands;

use Dybasedev\Keeper\Http\Kernel;
use Dybasedev\Keeper\Http\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServerManageCommand extends Command
{
    /**
     * @var Kernel
     */
    protected $handler;

    public $host;

    public $port;

    public $pidFile;

    public $options = [];

    /**
     * @var OutputInterface
     */
    private $output;

    public function configure()
    {
        $this->setName('server:control')
             ->setDescription('Server Controller')
             ->addArgument('signal', InputArgument::OPTIONAL,
                 'Send signal to master process, support: start, stop, restart, reload', 'start')
             ->addOption('host', 'H', InputOption::VALUE_OPTIONAL, 'Server host')
             ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Server port')
             ->addOption('pid_file', 'P', InputOption::VALUE_OPTIONAL, 'PID File path.')
             ->addOption('log_file', 'l', InputOption::VALUE_OPTIONAL, 'Log file path.')
             ->addOption('daemon', 'D', InputOption::VALUE_NONE)
             ->addOption('nodaemon', 'N', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        if ($host = $input->getOption('host')) {
            $this->host = $host;
        }

        if ($port = $input->getOption('port')) {
            $this->port = $port;
        }

        if ($pidFile = $input->getOption('pid_file')) {
            $this->pidFile = $this->options['pid_file'] = $pidFile;
        }

        if (!$this->pidFile) {
            if (isset($this->options['pid_file'])) {
                $this->pidFile = $this->options['pid_file'];
            }
        }

        if ($input->getOption('daemon')) {
            $this->options['daemonize'] = true;
        } elseif ($input->getOption('nodaemon')) {
            $this->options['daemonize'] = false;
        }

        if ($logFile = $input->getOption('log_file')) {
            $this->options['log_file'] = $logFile;
        }

        $signal = $input->getArgument('signal');
        switch (strtolower($signal)) {
            case 'start':
                return $this->startServer();
            case 'stop':
                return $this->stopServer();
            case 'restart':
                return $this->restartServer();
            case 'reload':
                return $this->reloadServer();
            default:
                return 1;
        }
    }

    public function setHandler($handler)
    {
        if ($handler instanceof Kernel) {
            $this->handler = $handler;

            return $this;
        }

        if (is_file($handler)) {
            $this->handler = require $handler;

            return $this;
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @param null $host
     * @param null $port
     *
     * @return \Dybasedev\Keeper\Server\Server|Server
     */
    protected function createServerInstance($host = null, $port = null)
    {
        return (new Server($host ?: $this->host, $port ?: $this->port))->setOptions($this->options);
    }

    protected function startServer()
    {
        $this->output->writeln("keeper: Start server <bg=green>successful</>");

        ob_start();
        $this->createServerInstance()->setHandler($this->getHandler())->start();
        ob_end_clean();

        return 0;
    }

    public function getHandler()
    {
        if ($this->handler instanceof \Closure) {
            $this->handler = ($this->handler)();
        }

        return $this->handler;
    }

    protected function stopServer()
    {
        if (is_file($this->pidFile)) {
            $pid = trim(file_get_contents($this->pidFile));

            posix_kill($pid, SIGTERM);
            $this->output->writeln("keeper: Stop server...");

            sleep(1);
        }

        $this->output->write("keeper: <bg=green>Successful</>\n");

        return 0;
    }

    protected function reloadServer()
    {
        if (is_file($this->pidFile)) {
            $pid = trim(file_get_contents($this->pidFile));

            posix_kill($pid, SIGUSR1);
            $this->output->writeln("keeper: Reload server workers over.");
        }

        return 0;
    }

    protected function restartServer()
    {
        $this->output->writeln("keeper: Restart progress open.");

        if ($this->stopServer() === 0 && $this->startServer() === 0) {
            $this->output->writeln("keeper: Restart over.");

            return 0;
        }

        return 3;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }
}