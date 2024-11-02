<?php

declare(strict_types=1);

namespace Algorithm;

use Heptacom\DependencyResolver\DependencyResolver as HeptacomDependencyResolver;
use Heptacom\DependencyResolver\Exception\CircularReferenceException as HeptacomCircularReferenceException;
use Heptacom\DependencyResolver\Exception\MissingReferenceException as HeptacomMissingReferenceException;
use Heptacom\DependencyResolver\ResolveBehaviour as HeptacomResolveBehaviour;

/**
 * Created by Anthony K GROSS.
 * User: anthony.k.gross@gmail.com
 * Date: 23/3/17
 * Time: 20:25 PM.
 *
 * @deprecated use \Heptacom\DependencyResolver\DependencyResolver instead
 * @see HeptacomDependencyResolver
 */
class DependencyResolver
{
    /**
     * @throws Exception\ResolveException
     */
    public static function resolve(array $tree, ?ResolveBehaviour $resolveBehaviour = null): array
    {
        try {
            $heptacomResolveBehaviour = null;

            if ($resolveBehaviour !== null) {
                $heptacomResolveBehaviour = new HeptacomResolveBehaviour(
                    $resolveBehaviour->isThrowOnCircularReference(),
                    $resolveBehaviour->isThrowOnMissingReference(),
                );
            }

            return (new HeptacomDependencyResolver())->resolve($tree, $heptacomResolveBehaviour);
        } catch (HeptacomCircularReferenceException $exception) {
            throw new Exception\CircularReferenceException($exception->item, $exception->dependency);
        } catch (HeptacomMissingReferenceException $exception) {
            throw new Exception\MissingReferenceException($exception->item, $exception->dependency);
        }
    }
}
