<?php
/**
 * ServerManageCommand.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Commands;

use Closure;
use Dybasedev\Keeper\Http\Interfaces\HttpService;
use Dybasedev\Keeper\Http\ProcessKernels\KeeperKernel;
use Dybasedev\Keeper\Server\HttpServer;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ServerManage extends Command
{
    public $host;

    public $port;

    public $pidFile;

    public $options = [];

    protected $serverInstanceCreator;

    protected $processKernelProvider;

    protected $httpService;

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
             ->addOption('pid', 'P', InputOption::VALUE_OPTIONAL, 'PID File path.')
             ->addOption('log', 'l', InputOption::VALUE_OPTIONAL, 'Log file path.')
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

    public function setServerInstanceCreator(Closure $creator)
    {
        $this->serverInstanceCreator = $creator;

        return $this;
    }

    /**
     * @param Closure $processKernelProvider
     *
     * @return ServerManage
     */
    public function setProcessKernelProvider(Closure $processKernelProvider)
    {
        $this->processKernelProvider = $processKernelProvider;

        return $this;
    }

    public function service($service)
    {
        $this->httpService = $service;

        return $this;
    }

    /**
     * @param null $host
     * @param null $port
     *
     * @return HttpServer
     */
    protected function createServerInstance($host = null, $port = null)
    {
        if (!$this->httpService) {
            throw new InvalidArgumentException('The HTTP service instance is necessary.');
        }

        $service = $this->httpService;
        if ($service instanceof Closure) {
            $service = ($service)();
        }

        if ($this->serverInstanceCreator) {
            return ($this->serverInstanceCreator)();
        }

        $server = new HttpServer($this->createProcessKernel($service));

        $host = $host ?: $this->host;
        $port = $port ?: $this->port;

        return $server->host($host ?: '0.0.0.0')->port($port ?: 11780)->setting($this->options);
    }

    protected function createProcessKernel(HttpService $service)
    {
        if ($this->processKernelProvider) {
            return ($this->processKernelProvider)($service);
        }

        return new KeeperKernel($service);
    }

    protected function startServer()
    {
        $this->output->writeln("keeper: Start server <bg=green>successful</>");

        ob_start();
        $this->createServerInstance()->start();
        ob_end_clean();

        return 0;
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