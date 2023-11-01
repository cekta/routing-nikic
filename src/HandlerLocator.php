<?php

declare(strict_types=1);

namespace Cekta\Routing\Nikic;

use Psr\Http\Server\RequestHandlerInterface;

interface HandlerLocator
{
    public function get(string $handleName): RequestHandlerInterface;
}
