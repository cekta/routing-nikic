<?php
declare(strict_types=1);

namespace Cekta\Routing\Nikic;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser;
use FastRoute\RouteParser\Std;
use InvalidArgumentException;
use RuntimeException;

class DispatcherBuilder
{
    public const STRATEGY_CHARCOUNTBASED = 0;
    public const STRATEGY_GROUPCOUNTBASED = 1;
    public const STRATEGY_GROUPPOSBASED = 2;
    public const STRATEGY_MARKBASED = 3;
    private const strategies = [
        [
            'Dispatcher' => '\FastRoute\Dispatcher\CharCountBased',
            'DataGenerator' => '\FastRoute\DataGenerator\CharCountBased',
        ],
        [
            'Dispatcher' => '\FastRoute\Dispatcher\GroupCountBased',
            'DataGenerator' => '\FastRoute\DataGenerator\GroupCountBased',
        ],
        [
            'Dispatcher' => '\FastRoute\Dispatcher\GroupPosBased',
            'DataGenerator' => '\FastRoute\DataGenerator\GroupPosBased',
        ],
        [
            'Dispatcher' => '\FastRoute\Dispatcher\MarkBased',
            'DataGenerator' => '\FastRoute\DataGenerator\MarkBased',
        ],
    ];
    /**
     * @var int
     */
    private $strategy = self::STRATEGY_GROUPCOUNTBASED;
    /**
     * @var RouteParser
     */
    private $parser;
    /**
     * @var array
     */
    private $routes;
    /**
     * @var string
     */
    private $cacheFile;

    public function setStrategy(int $strategy): self
    {
        if (!array_key_exists($strategy, self::strategies)) {
            throw new InvalidArgumentException('Strategy must be in the strategies list');
        }
        $this->strategy = $strategy;
        return $this;
    }

    public function setCacheFile(string $cacheFile): self
    {
        $this->cacheFile = $cacheFile;
        return $this;
    }

    public function setRouteParser(RouteParser $parser): self
    {
        $this->parser = $parser;
        return $this;
    }

    public function get(string $route, string $handler, string ...$middlewares)
    {
        return $this->addRoute('GET', $route, new Handler($handler, ...$middlewares));
    }

    public function post(string $route, string $handler, string ...$middlewares)
    {
        return $this->addRoute('POST', $route, new Handler($handler, ...$middlewares));
    }

    public function patch(string $route, string $handler, string ...$middlewares)
    {
        return $this->addRoute('PATCH', $route, new Handler($handler, ...$middlewares));
    }

    public function put(string $route, string $handler, string ...$middlewares)
    {
        return $this->addRoute('PUT', $route, new Handler($handler, ...$middlewares));
    }

    public function delete(string $route, string $handler, string ...$middlewares)
    {
        return $this->addRoute('DELETE', $route, new Handler($handler, ...$middlewares));
    }

    public function build(): Dispatcher
    {
        if (!empty($this->cacheFile) && is_readable($this->cacheFile)) {
            $data = $this->getDataFromCacheFile();
        } else {
            $data = $this->getDataFromCollector();
        }
        $name = self::strategies[$this->strategy]['Dispatcher'];
        return new $name($data);
    }

    private function getDataFromCacheFile(): array
    {
        /** @noinspection PhpIncludeInspection */
        $data = require $this->cacheFile;
        if (!is_array($data)) {
            throw new RuntimeException('Invalid cache file "' . $this->cacheFile . '"');
        }
        return $data;
    }

    private function getDataFromCollector(): array
    {
        $collector = $this->getCollector();
        $data = $collector->getData();
        if (!empty($this->cacheFile)) {
            file_put_contents(
                $this->cacheFile,
                '<?php return ' . var_export($data, true) . ';'
            );
        }
        return $data;
    }

    private function getCollector(): RouteCollector
    {
        $collector = new RouteCollector($this->getRouteParser(), $this->getDataGenerator());
        foreach ($this->routes as $route) {
            $collector->addRoute($route['method'], $route['route'], $route['handler']);
        }
        return $collector;
    }

    private function getDataGenerator(): DataGenerator
    {
        $name = self::strategies[$this->strategy]['DataGenerator'];
        return new $name();
    }

    private function getRouteParser(): RouteParser
    {
        return $this->parser ?? new Std();
    }

    private function addRoute(string $method, string $route, Handler $handler): self
    {
        $this->routes[] = [
            'method' => $method,
            'route' => $route,
            'handler' => $handler
        ];
        return $this;
    }

}
