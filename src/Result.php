<?php
declare(strict_types=1);

namespace Cekta\Routing\Nikic;

use Cekta\Routing\ResultInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Result implements ResultInterface
{
    /**
     * @var RequestHandlerInterface
     */
    private $handler;
    /**
     * @var array
     */
    private $attributes;
    /**
     * @var MiddlewareInterface[]
     */
    private $middlewares;

    public function __construct(RequestHandlerInterface $handler, array $attributes, MiddlewareInterface ...$middlewares)
    {
        $this->handler = $handler;
        $this->attributes = $attributes;
        $this->middlewares = $middlewares;
    }

    public function getHandler(): RequestHandlerInterface
    {
        return $this->handler;
    }

    /**
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
