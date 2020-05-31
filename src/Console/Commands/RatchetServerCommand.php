<?php

namespace Prosystemsc\LaravelRatchet\Console\Commands;

use Illuminate\Console\Command;
use Prosystemsc\LaravelRatchet\BlackList\IpBlackList;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class RatchetServerCommand extends Command
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'ratchet:serve';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start Ratchet Server';

    /**
     * Server host.
     *
     * @var string
     */
    protected $host;

    /**
     * Server port.
     *
     * @var int
     */
    protected $port;

    /**
     * The class to use for the server.
     *
     * @var string
     */
    protected $class;

    /**
     * The type of server to boot.
     *
     * @var string
     */
    protected $driver;

    /**
     * The mutable server instance.
     *
     * @var mixed
     */
    protected $serverInstance;

    /**
     * The original instance of $this->class
     */
    protected $ratchetServer;

    protected $blacklist;

    /**
     * Get the console command options.
     *
     * @return array
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->host = config('ratchet.host', 'localhost');

        $this->port = intval(config('ratchet.port', '9090'));

        $this->class = config('ratchet.class', '\Prosystemsc\LaravelRatchet\Examples\Pusher::class');

        $this->driver = config('ratchet.driver', 'IoServer');

        $this->startServer();
    }

    /**
     * Start the appropriate server.
     *
     * @param string $driver
     */
    private function startServer()
    {
        $this->info(sprintf('Starting %s server on: %s:%d', $this->driver, $this->host, $this->port));

        $this->createServerInstance();

        $this->{'start' . $this->driver}()->run();
    }

    /**
     * Get/generate the server instance from the class provided.
     */
    private function createServerInstance()
    {
        if (!$this->serverInstance instanceof $this->class) {
            $class = $this->class;
            $this->serverInstance = $this->ratchetServer = new $class($this);
        }
    }

    /**
     * Decorate a server instance with a blacklist instance and block any blacklisted addresses.
     */
    private function bootWithBlacklist()
    {
        $this->serverInstance = new IpBlackList($this->serverInstance);
        foreach (config('ratchet.blackList') as $host) {
            $this->serverInstance->blockAddress($host);
        }

    }

    private function startIoServer()
    {
        $this->bootWithBlacklist();

        return $this->bootIoServer();
    }

    public function bootIoServer()
    {
        $instance = $this->serverInstance;
        $server = IoServer::factory(new HttpServer(new WsServer($instance)), $this->port);

        $server->loop->addPeriodicTimer(config('ratchet.periodsync', 5), function () use ($instance) {
            foreach ($instance->_decorating->timesync as $client) {
                $client->send('{"name":"timeSync","msg":' . strtotime(date('Y-m-d H:i:s')) . '}');
            }
        });

        return $server;
    }

}
