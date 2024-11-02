<?php

declare(strict_types=1);

namespace Heptacom\DependencyResolver;

final class ResolveBehaviour
{
    public function __construct(
        public readonly bool $throwOnCircularReference = false,
        public readonly bool $throwOnMissingReference = false,
    ) {
    }

    public function withThrowOnCircularReference(bool $throwOnCircularReference): self
    {
        return new self(
            $throwOnCircularReference,
            $this->throwOnMissingReference,
        );
    }

    public function withThrowOnMissingReference(bool $throwOnMissingReference): self
    {
        return new self(
            $this->throwOnCircularReference,
            $throwOnMissingReference,
        );
    }
}
