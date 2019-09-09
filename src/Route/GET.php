<?php
declare(strict_types=1);

namespace Cekta\Routing\Nikic\Route;

class GET extends AbstractRoute
{
    public function __construct(string $route, string $handler, string ...$middlewares)
    {
        parent::__construct('GET', $route, $handler, ...$middlewares);
    }
}
