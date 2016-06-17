<?php
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
