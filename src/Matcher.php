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
     * @var ProviderHandler
     */
    private $providerHandler;
    /**
     * @var ProviderMiddleware
     */
    private $providerMiddleware;
    /**
     * @var Handler
     */
    private $notFound;

    public function __construct(
        Handler $notFound,
        Dispatcher $dispatcher,
        ProviderHandler $providerHandler,
        ProviderMiddleware $providerMiddleware
    ) {
        $this->dispatcher = $dispatcher;
        $this->providerHandler = $providerHandler;
        $this->providerMiddleware = $providerMiddleware;
        $this->notFound = $notFound;
    }

    public function match(ServerRequestInterface $request): ResultInterface
    {
        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $request->getRequestTarget());
        if ($routeInfo[0] == Dispatcher::FOUND) {
            $result = $this->getResultFromRoute($routeInfo);
        } else {
            $middlewares = $this->createMiddlewares(...$this->notFound->getMiddlewares());
            $result = new Result($this->providerHandler->get($this->notFound->getHandler()), [], ...$middlewares);
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
            $this->providerHandler->get($handler->getHandler()),
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
            $middlewares[] = $this->providerMiddleware->get($middlewareName);
        };
        return $middlewares;
    }
}
