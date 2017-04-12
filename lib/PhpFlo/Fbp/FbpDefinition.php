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
namespace PhpFlo\Fbp;

use PhpFlo\Common\DefinitionInterface;
use PhpFlo\Common\FbpDefinitionsInterface;

/**
 * Class FbpDefinition
 *
 * @package PhpFlo\Fbp
 * @author Marc Aschmann <maschmann@gmail.com>
 */
class FbpDefinition implements DefinitionInterface, FbpDefinitionsInterface
{
    /**
     * @var array
     */
    private $schema;

    /**
     * FbpDefinition constructor.
     *
     * @param array $definition
     */
    public function __construct(array $definition = [])
    {
        $this->schema = [
            self::PROPERTIES_LABEL => [
                'name' => '',
            ],
            self::INITIALIZERS_LABEL => [],
            self::PROCESSES_LABEL => [],
            self::CONNECTIONS_LABEL => [],
        ];

        $this->definition($definition);
    }

    /**
     * @param array $definition
     * @return DefinitionInterface
     */
    public function definition(array $definition) : DefinitionInterface
    {
        $this->schema = array_replace_recursive(
            $this->schema,
            $definition
        );

        return $this;
    }

    /**
     * @return array
     */
    public function properties() : array
    {
        return $this->schema[self::PROPERTIES_LABEL];
    }

    /**
     * @return array
     */
    public function initializers() : array
    {
        return $this->schema[self::INITIALIZERS_LABEL];
    }

    /**
     * @return array
     */
    public function processes() : array
    {
        return $this->schema[self::PROCESSES_LABEL];
    }

    /**
     * @return array
     */
    public function connections() : array
    {
        return $this->schema[self::CONNECTIONS_LABEL];
    }

    /**
     * @return string
     */
    public function name() : string
    {
        return $this->schema[self::PROPERTIES_LABEL]['name'];
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    public function toJson() : string
    {
        return FbpDumper::toJson($this->schema);
    }

    /**
     * @return string
     */
    public function toYaml() : string
    {
        return FbpDumper::toYaml($this->schema);
    }

    /**
     * @return string
     */
    public function toFbp() : string
    {
        return FbpDumper::toFbp($this->schema);
    }
}
