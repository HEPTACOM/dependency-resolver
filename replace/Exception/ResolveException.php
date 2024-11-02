<?php

declare(strict_types=1);

namespace Algorithm\Exception;

/**
 * @deprecated use \Heptacom\DependencyResolver\Exception\ResolveExceptionContract instead
 * @see \Heptacom\DependencyResolver\Exception\ResolveExceptionContract
 */
abstract class ResolveException extends \RuntimeException
{
    public function __construct(
        private readonly int|string $item,
        private readonly int|string $dependency,
        string $message,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getItem(): int|string
    {
        return $this->item;
    }

    public function getDependency(): int|string
    {
        return $this->dependency;
    }
}
