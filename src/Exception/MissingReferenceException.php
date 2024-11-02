<?php

declare(strict_types=1);

namespace Heptacom\DependencyResolver\Exception;

final class MissingReferenceException extends ResolveExceptionContract
{
    public function __construct(string|int $item, string|int $dependency, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            $item,
            $dependency,
            \sprintf('Missing dependency: %s -> %s', $item, $dependency),
            $code,
            $previous,
        );
    }
}
