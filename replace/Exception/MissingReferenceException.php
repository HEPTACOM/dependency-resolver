<?php

declare(strict_types=1);

namespace Algorithm\Exception;

/**
 * @deprecated use \Heptacom\DependencyResolver\Exception\MissingReferenceException instead
 * @see \Heptacom\DependencyResolver\Exception\MissingReferenceException
 */
class MissingReferenceException extends ResolveException
{
    public function __construct(string|int $item, string|int $dependency, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($item, $dependency, \sprintf('Missing dependency: %s -> %s', $item, $dependency), $code, $previous);
    }
}
