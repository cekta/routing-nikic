<?php
declare(strict_types=1);

namespace Cekta\Routing\Nikic\Route;

abstract class AbstractRoute
{
    /**
     * @var string
     */
    private $method;
    /**
     * @var string
     */
    private $route;
    /**
     * @var string
     */
    private $handler;
    /**
     * @var string[]
     */
    private $middlewares;

    public function __construct(string $method, string $route, string $handler, string ...$middlewares)
    {
        $this->method = $method;
        $this->route = $route;
        $this->handler = $handler;
        $this->middlewares = $middlewares;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getHandlerData(): array
    {
        return array_merge([$this->handler], $this->middlewares);
    }
}
