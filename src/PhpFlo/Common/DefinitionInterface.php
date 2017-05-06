<?php
/*
 * This file is part of the <package> package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace PhpFlo\Common;

/**
 * Interface DefinitionInterface
 *
 * @package PhpFlo\Common
 * @author Marc Aschmann <maschmann@gmail.com>
 */
interface DefinitionInterface
{
    /**
     * @param array $definition
     * @return DefinitionInterface
     */
    public function definition(array $definition): DefinitionInterface;

    /**
     * @return array
     */
    public function properties(): array;

    /**
     * @return array
     */
    public function initializers(): array;

    /**
     * @return array
     */
    public function processes(): array;

    /**
     * @return array
     */
    public function connections(): array;

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return string
     */
    public function toJson(): string;

    /**
     * @return string
     */
    public function toYaml(): string;

    /**
     * @return string
     */
    public function toFbp(): string;

    /**
     * @return string
     */
    public function name(): string;
}
