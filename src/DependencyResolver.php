<?php

declare(strict_types=1);

namespace Heptacom\DependencyResolver;

final class DependencyResolver
{
    /**
     * @param array<array-key, array-key[]> $tree
     *
     * @return array-key[]
     *
     * @throws Exception\ResolveExceptionContract
     */
    public function resolve(array $tree, ?ResolveBehaviour $resolveBehaviour = null): array
    {
        $resolveBehaviour = $resolveBehaviour ?? (new ResolveBehaviour())->withThrowOnCircularReference(true);
        $resolved = [];
        $unresolved = [];

        // Resolve dependencies for each table
        foreach (\array_keys($tree) as $table) {
            [
                'resolved' => $resolved,
                'unresolved' => $unresolved,
                'returnImmediately' => $returnImmediately,
            ] = self::resolver($table, $tree, $resolved, $unresolved, $resolveBehaviour);

            if ($returnImmediately) {
                return $resolved;
            }
        }

        return $resolved;
    }

    /**
     * @param array<array-key, array-key[]> $tree
     *
     * @return array{
     *     resolved: array-key[],
     *     unresolved: array-key[],
     *     returnImmediately: bool
     * }
     *
     * @throws Exception\ResolveExceptionContract
     */
    private function resolver(
        string|int $item,
        array $tree,
        array $resolved,
        array $unresolved,
        ResolveBehaviour $resolveBehaviour,
    ): array {
        $unresolved[] = $item;

        foreach ($tree[$item] as $dep) {
            if (!\array_key_exists($dep, $tree)) {
                if ($resolveBehaviour->throwOnMissingReference) {
                    throw new Exception\MissingReferenceException($item, $dep);
                }

                return [
                    'resolved' => $resolved,
                    'unresolved' => $unresolved,
                    'returnImmediately' => true,
                ];
            }

            if (\in_array($dep, $resolved, true)) {
                continue;
            }

            if (\in_array($dep, $unresolved, true)) {
                if ($resolveBehaviour->throwOnCircularReference) {
                    throw new Exception\CircularReferenceException($item, $dep);
                }

                return [
                    'resolved' => $resolved,
                    'unresolved' => $unresolved,
                    'returnImmediately' => true,
                ];
            }

            $unresolved[] = $dep;
            [
                'resolved' => $resolved,
                'unresolved' => $unresolved,
                'returnImmediately' => $returnImmediately,
            ] = self::resolver($dep, $tree, $resolved, $unresolved, $resolveBehaviour);

            if ($returnImmediately) {
                return [
                    'resolved' => $resolved,
                    'unresolved' => $unresolved,
                    'returnImmediately' => $returnImmediately,
                ];
            }
        }

        // Add $item to $resolved if it's not already there
        if (!\in_array($item, $resolved, true)) {
            $resolved[] = $item;
        }

        // Remove all occurrences of $item in $unresolved
        while (($index = \array_search($item, $unresolved, true)) !== false) {
            unset($unresolved[$index]);
        }

        return [
            'resolved' => $resolved,
            'unresolved' => $unresolved,
            'returnImmediately' => false,
        ];
    }
}
