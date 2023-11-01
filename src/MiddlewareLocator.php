<?php

declare(strict_types=1);

namespace Cekta\Routing\Nikic;

use Psr\Http\Server\MiddlewareInterface;

interface MiddlewareLocator
{
    public function get(string $middlewareName): MiddlewareInterface;
}
