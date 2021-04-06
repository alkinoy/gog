<?php

declare(strict_types=1);

namespace App\Services;

use Domnikl\Statsd\Client;
use Domnikl\Statsd\Connection\UdpSocket;

class StatsDClient
{
    private UdpSocket $connection;
    private Client $statsd;

    /**
     * StatsDClient constructor.
     */
    public function __construct(string $namespace, string $statsDHost, int $port)
    {
        $this->connection = new UdpSocket($statsDHost, $port);
        $this->statsd = new Client($this->connection, $namespace);
    }

    public function startTiming(string $indicatorName): void
    {
        $this->statsd->startTiming($indicatorName);
    }

    public function endTiming(string $indicatorName): void
    {
        $this->statsd->endTiming($indicatorName);
    }
}