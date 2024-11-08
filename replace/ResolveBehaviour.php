<?php

declare(strict_types=1);

namespace Algorithm;

/**
 * @deprecated use \Heptacom\DependencyResolver\ResolveBehaviour instead
 * @see \Heptacom\DependencyResolver\ResolveBehaviour
 */
class ResolveBehaviour
{
    private bool $throwOnCircularReference = true;

    private bool $throwOnMissingReference = false;

    public static function create(): self
    {
        return new self();
    }

    public function isThrowOnCircularReference(): bool
    {
        return $this->throwOnCircularReference;
    }

    public function setThrowOnCircularReference(bool $throwOnCircularReference): self
    {
        $this->throwOnCircularReference = $throwOnCircularReference;

        return $this;
    }

    public function isThrowOnMissingReference(): bool
    {
        return $this->throwOnMissingReference;
    }

    public function setThrowOnMissingReference(bool $throwOnMissingReference): self
    {
        $this->throwOnMissingReference = $throwOnMissingReference;

        return $this;
    }
}
