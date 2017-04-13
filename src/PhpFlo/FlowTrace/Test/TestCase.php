<?php
declare(strict_types=1);
/*
 * This file is part of the phpflo/phpflo-flowtrace package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo\FlowTrace\Test;

use PhpFlo\Common\Test\TestUtilityTrait;

/**
 * Class TestCase
 *
 * @package PhpFlo\Test
 * @author Marc Aschmann <maschmann@gmail.com>
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    use TestUtilityTrait;
}
