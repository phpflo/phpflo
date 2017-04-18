<?php
/*
 * This file is part of the phpflo\phpflo-fbp package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace PhpFlo\Common;

use PhpFlo\Common\Exception\LoaderException;

/**
 * Interface LoaderInterface
 *
 * @package PhpFlo\Common
 * @author Marc Aschmann <maschmann@gmail.com>
 */
interface LoaderInterface
{
    /**
     * @param string $file filename/path
     * @return DefinitionInterface
     * @throws LoaderException
     */
    public static function load(string $file): DefinitionInterface;
}
