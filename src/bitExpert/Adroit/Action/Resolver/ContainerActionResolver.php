<?php

/**
 * This file is part of the Adroit package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types = 1);

namespace bitExpert\Adroit\Action\Resolver;

use bitExpert\Adroit\Resolver\ContainerResolver;

/**
 * Implementation of an {@link \bitExpert\Adroit\Action\Resolver\ActionResolver} which will
 * pull the actions from a "container-aware" service.
 */
class ContainerActionResolver extends ContainerResolver implements ActionResolver
{

}
