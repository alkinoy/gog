<?php


namespace App\Tests\Behat;


use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class PingContext implements Context
{
    /** @var KernelInterface */
    private $kernel;

    /** @var Response|null */
    private $response;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @When a ping scenario sends a request to :path
     */
    public function aPingScenarioSendsARequestTo(string $path): void
    {
        $this->response = $this->kernel->handle(Request::create($path, 'GET'));
    }

    /**
     * @Then the response :resp should be received
     */
    public function theResponseShouldBeReceived(string $resp): void
    {
        if ($this->response === null || $resp !== $this->response->getContent()) {
            throw new \RuntimeException('No response received');
        }
    }
}