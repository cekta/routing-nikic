<?php

declare(strict_types=1);

namespace Cekta\Routing\Nikic;

use Cekta\Routing\MatcherInterface;
use Cekta\Routing\ResultInterface;
use FastRoute\Dispatcher;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class Matcher implements MatcherInterface
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;
    /**
     * @var HandlerLocator
     */
    private $handlerLocator;
    /**
     * @var MiddlewareLocator
     */
    private $middlewareLocator;
    /**
     * @var Handler
     */
    private $notFound;

    public function __construct(
        Handler $notFound,
        Dispatcher $dispatcher,
        HandlerLocator $handlerLocator,
        MiddlewareLocator $middlewareLocator
    ) {
        $this->dispatcher = $dispatcher;
        $this->handlerLocator = $handlerLocator;
        $this->middlewareLocator = $middlewareLocator;
        $this->notFound = $notFound;
    }

    public function match(ServerRequestInterface $request): ResultInterface
    {
        $uri = $request->getRequestTarget();
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $uri);
        if ($routeInfo[0] == Dispatcher::FOUND) {
            $result = $this->getResultFromRoute($routeInfo);
        } else {
            $middlewares = $this->createMiddlewares(...$this->notFound->getMiddlewares());
            $result = new Result($this->handlerLocator->get($this->notFound->getHandler()), [], ...$middlewares);
        }
        return $result;
    }

    private function getResultFromRoute(array $routeInfo): Result
    {
        $handler = $routeInfo[1];
        if (!$handler instanceof Handler) {
            throw new InvalidArgumentException('Route info must contain Handler');
        }
        return new Result(
            $this->handlerLocator->get($handler->getHandler()),
            $routeInfo[2],
            ...$this->createMiddlewares(...$handler->getMiddlewares())
        );
    }

    /**
     * @noinspection PhpDocSignatureInspection
     * @param string[] ...$middlewareNames
     * @return MiddlewareInterface[]
     */
    private function createMiddlewares(string ...$middlewareNames): array
    {
        $middlewares = [];
        foreach ($middlewareNames as $middlewareName) {
            $middlewares[] = $this->middlewareLocator->get($middlewareName);
        };
        return $middlewares;
    }
}
