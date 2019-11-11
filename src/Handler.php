<?php

declare(strict_types=1);

namespace Cekta\Routing\Nikic;

class Handler
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

    public static function __set_state(array $state): self
    {
        return new static($state['handler'], ...$state['middlewares']);
    }


    public function getHandler(): string
    {
        return $this->handler;
    }

    /**
     * @return string[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
