<?php

namespace CKSource\Bundle\CKFinderBundle\Polyfill;

use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;

/**
 * Class CommandResolver
 *
 * @deprecated
 */
class CommandResolver extends \CKSource\CKFinder\CommandResolver implements ArgumentResolverInterface {}
