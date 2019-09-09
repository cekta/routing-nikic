<?php
declare(strict_types=1);

namespace Cekta\Routing\Nikic;

use Cekta\Routing\MatcherInterface;
use Cekta\Routing\Nikic\Route\AbstractRoute;
use Cekta\Routing\RouteInterface;
use FastRoute\RouteParser\Std;
use Psr\Http\Message\ServerRequestInterface;
use FastRoute\RouteCollector;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\Dispatcher;

class Matcher implements MatcherInterface
{
    /**
     * @var GroupCountBased
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
     * @var NotFound
     */
    private $notFound;

    public function __construct(
        ProviderHandler $providerHandler,
        ProviderMiddleware $providerMiddleware,
        NotFound $notFound,
        AbstractRoute ...$routes
    ) {
        $this->providerHandler = $providerHandler;
        $this->providerMiddleware = $providerMiddleware;
        $this->notFound = $notFound;
        $collector = new RouteCollector(new Std(), new \FastRoute\DataGenerator\GroupCountBased());
        foreach ($routes as $route) {
            $collector->addRoute($route->getMethod(), $route->getRoute(), $route->getHandlerData());
        }
        $this->dispatcher = new GroupCountBased($collector->getData());
    }

    public function match(ServerRequestInterface $request): RouteInterface
    {
        $uri = $request->getRequestTarget();
        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::METHOD_NOT_ALLOWED:
            case Dispatcher::NOT_FOUND:
                $middlewares = [];
                foreach ($this->notFound->getMiddlewares() as $middleware) {
                    $middlewares[] = $this->providerMiddleware->get($middleware);
                }
                $route = new Route($this->providerHandler->get($this->notFound->getHandler()), [], ...$middlewares);
                break;
            default:
                $handlerName = array_shift($routeInfo[1]);
                $middlewares = [];
                foreach ($routeInfo[1] as $middlewareName) {
                    $middlewares[] = $this->providerMiddleware->get($middlewareName);
                };
                $route = new Route($this->providerHandler->get($handlerName), $routeInfo[2], ...$middlewares);
                break;
        }
        return $route;
    }
}
