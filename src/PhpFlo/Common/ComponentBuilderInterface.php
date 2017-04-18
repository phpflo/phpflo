<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace PhpFlo\Common;

use PhpFlo\Common\Exception\InvalidDefinitionException;

/**
 * Interface ComponentBuilderInterface
 *
 * @package PhpFlo\Common
 * @author Marc Aschmann <maschmann@gmail.com>
 */
interface ComponentBuilderInterface
{
    /**
     * @param string $component
     * @return ComponentInterface
     * @throws InvalidDefinitionException
     */
    public function build(string $component): ComponentInterface;
}
