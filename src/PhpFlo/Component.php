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
 * Class Component
 *
 * @package PhpFlo
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
class Component implements ComponentInterface
{
    /**
     * @var array
     */
    public $inPorts = [];

    /**
     * @var array
     */
    public $outPorts = [];

    /**
     * @var string
     */
    protected $description = "";

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
