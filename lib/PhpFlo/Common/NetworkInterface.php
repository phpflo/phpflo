<?php
/*
 * This file is part of the <phpflo/phpflo> package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo\Common;

use PhpFlo\Exception\InvalidDefinitionException;
use PhpFlo\Graph;

/**
 * Interface NetworkInterface
 *
 * @package PhpFlo\Common
 * @author Marc Aschmann <maschmann@gmail.com>
 */
interface NetworkInterface
{
     /**
     * @return null|Graph
     */
    public function getGraph();

    /**
     * @param mixed $data
     * @param string $node
     * @param string $port
     * @return $this
     * @throws InvalidDefinitionException
     */
    public function addInitial($data, $node, $port);

    /**
     * Cleanup network state after runs.
     *
     * @return $this
     */
    public function shutdown();
}
