<?php

declare(strict_types=1);

namespace Heptacom\DependencyResolver\Tests;

use Heptacom\DependencyResolver\DependencyResolver;
use Heptacom\DependencyResolver\Exception\CircularReferenceException;
use Heptacom\DependencyResolver\Exception\MissingReferenceException;
use Heptacom\DependencyResolver\ResolveBehaviour;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CircularReferenceException::class)]
#[CoversClass(DependencyResolver::class)]
#[CoversClass(MissingReferenceException::class)]
#[CoversClass(ResolveBehaviour::class)]
class DependencyResolverTest extends TestCase
{
    public function testCircleDependenciesCase1(): void
    {
        $this->expectException(CircularReferenceException::class);
        $this->expectExceptionMessage('Circular dependency: C -> A');

        $tree = [
            'A' => ['B'],
            'B' => ['C'],
            'C' => ['A'],
        ];
        (new DependencyResolver())->resolve($tree);
    }

    public function testCircleDependenciesCaseThrowBehaviour(): void
    {
        $this->expectException(CircularReferenceException::class);
        $this->expectExceptionMessage('Circular dependency: B -> A');

        $tree = [
            'A' => ['B'],
            'B' => ['A'],
        ];
        (new DependencyResolver())->resolve(
            $tree,
            new ResolveBehaviour(throwOnCircularReference: true),
        );
    }

    public function testCircleDependenciesCaseNotThrowBehaviour(): void
    {
        $tree = [
            'A' => ['B'],
            'B' => ['A'],
        ];
        $resolution = (new DependencyResolver())->resolve(
            $tree,
            new ResolveBehaviour(throwOnCircularReference: false),
        );
        static::assertEquals($resolution, []);
    }

    public function testCircleDependenciesCase2(): void
    {
        $this->expectException(CircularReferenceException::class);
        $this->expectExceptionMessage('Circular dependency: B -> A');

        $tree = [
            'A' => ['E'],
            'B' => ['A'],
            'C' => ['B'],
            'D' => ['C', 'A'],
            'E' => ['C', 'B'],
        ];
        (new DependencyResolver())->resolve($tree);
    }

    public function testResolverCase1(): void
    {
        $tree = [
            'A' => ['B'],
            'B' => ['C'],
            'C' => [],
        ];
        $resolution = (new DependencyResolver())->resolve($tree);
        static::assertEquals($resolution, ['C', 'B', 'A']);
    }

    public function testResolverCase2(): void
    {
        $tree = [
            'A' => ['C'],
            'B' => ['C'],
            'C' => [],
            'D' => ['B'],
        ];
        $resolution = (new DependencyResolver())->resolve($tree);
        static::assertEquals($resolution, ['C', 'A', 'B', 'D']);
    }

    public function testResolverCase3(): void
    {
        $tree = [
            'A' => [],
            'B' => ['A'],
            'C' => ['B'],
            'D' => ['C', 'A'],
            'E' => ['C', 'B'],
        ];
        $resolution = (new DependencyResolver())->resolve($tree);
        static::assertEquals($resolution, ['A', 'B', 'C', 'D', 'E']);
    }

    public function testResolverCase4(): void
    {
        $tree = [
            'A' => [],
            'B' => ['A'],
            'D' => ['C', 'A'],
            'C' => ['B'],
            'E' => ['C', 'B'],
        ];
        $resolution = (new DependencyResolver())->resolve($tree);
        static::assertEquals($resolution, ['A', 'B', 'C', 'D', 'E']);
    }

    public function testMissingDependenciesCaseThrowBehaviour(): void
    {
        $this->expectException(MissingReferenceException::class);
        $this->expectExceptionMessage('Missing dependency: B -> A');

        $tree = [
            'B' => ['A'],
        ];
        (new DependencyResolver())->resolve(
            $tree,
            new ResolveBehaviour(throwOnMissingReference: true),
        );
    }

    public function testMissingDependenciesCaseNotThrowBehaviour(): void
    {
        $tree = [
            'B' => ['A'],
        ];
        $resolution = (new DependencyResolver())->resolve(
            $tree,
            new ResolveBehaviour(throwOnMissingReference: false),
        );
        static::assertEquals($resolution, []);
    }
}
