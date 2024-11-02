<?php

declare(strict_types=1);

namespace Algorithm\Exception;

/**
 * @deprecated use \Heptacom\DependencyResolver\Exception\CircularReferenceException instead
 * @see \Heptacom\DependencyResolver\Exception\CircularReferenceException
 */
class CircularReferenceException extends ResolveException
{
    public function __construct(string|int $item, string|int $dependency, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($item, $dependency, \sprintf('Circular dependency: %s -> %s', $item, $dependency), $code, $previous);
    }
}
