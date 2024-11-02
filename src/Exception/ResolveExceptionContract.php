<?php

declare(strict_types=1);

namespace Heptacom\DependencyResolver\Exception;

abstract class ResolveExceptionContract extends \RuntimeException
{
    public function __construct(
        public readonly int|string $item,
        public readonly int|string $dependency,
        string $message,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
