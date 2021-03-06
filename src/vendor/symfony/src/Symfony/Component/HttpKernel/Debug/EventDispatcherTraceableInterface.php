<?php

namespace Symfony\Component\HttpKernel\Debug;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
interface EventDispatcherTraceableInterface
{
    /**
     * Gets the called listeners.
     *
     * @return array An array of called listeners
     */
    function getCalledListeners();

    /**
     * Gets the not called listeners.
     *
     * @return array An array of not called listeners
     */
    function getNotCalledListeners();
}
