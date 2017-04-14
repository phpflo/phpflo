<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Henri Bergius <henri.bergius@iki.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);
namespace PhpFlo\Core;

use PhpFlo\Common\ComponentInterface;

/**
 * Class Component
 *
 * @package PhpFlo\Core
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
class Component implements ComponentInterface
{
    use ComponentTrait;
}
