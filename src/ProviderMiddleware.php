<?php
declare(strict_types=1);

namespace Cekta\Routing\Nikic;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;

class ProviderMiddleware
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get(string $middlewareName): MiddlewareInterface
    {
        return $this->container->get($middlewareName);
    }
}
