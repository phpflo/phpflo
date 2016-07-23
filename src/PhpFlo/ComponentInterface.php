<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Henri Bergius <henri.bergius@iki.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo;

/**
 * Interface ComponentInterface
 *
 * @package PhpFlo
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
interface ComponentInterface
{
    /**
     * @return string
     */
    public function getDescription();
}
