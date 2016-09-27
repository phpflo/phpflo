<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Henri Bergius <henri.bergius@iki.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo\Common;

use PhpFlo\Interaction\PortRegistry;

/**
 * Interface ComponentInterface
 *
 * @package PhpFlo\Common
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
interface ComponentInterface
{
    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return PortRegistry
     */
    public function inPorts();

    /**
     * @return PortRegistry
     */
    public function outPorts();

    /**
     * Detach all sockets, disconnect all ports.
     *
     * @return $this;
     */
    public function shutdown();
}
