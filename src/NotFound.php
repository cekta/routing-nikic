<?php
declare(strict_types=1);

namespace Cekta\Routing\Nikic;

class NotFound
{
    /**
     * @var string
     */
    private $handler;
    /**
     * @var string[]
     */
    private $middlewares;

    public function __construct(string $handler, string ...$middlewares)
    {
        $this->handler = $handler;
        $this->middlewares = $middlewares;
    }

    public function getHandler(): string
    {
        return $this->handler;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
