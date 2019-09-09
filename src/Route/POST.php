<?php
declare(strict_types=1);

namespace Cekta\Routing\Nikic\Route;

class POST extends AbstractRoute
{
    public function __construct( string $route, string $handler, string ...$middlewares)
    {
        parent::__construct('POST', $route, $handler, ...$middlewares);
    }
}
