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
    public $inPorts = [];
    public $outPorts = [];
    protected $description = "";

    public function getDescription()
    {
        return $this->description;
    }
}
