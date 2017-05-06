<?php
namespace PhpFlo\Fbp\Tests;

use PhpFlo\Fbp\FbpDefinition;
use PhpFlo\Fbp\Test\TestCase;

class FbpDefinitionTest extends TestCase
{
    public function testInstance()
    {
        $definition = new FbpDefinition();
        $this->assertInstanceOf('PhpFlo\Fbp\FbpDefinition', $definition);
        $this->assertEquals(
            [
                'properties' => [
                    'name' => '',
                ],
                'initializers' => [],
                'processes' => [],
                'connections' => [],
            ],
            $definition->toArray()
        );
    }

    public function testAccessors()
    {
        $definition = new FbpDefinition($this->getData());

        $this->assertTrue(is_array($definition->connections()));
        $this->assertTrue(is_array($definition->initializers()));
        $this->assertTrue(is_array($definition->processes()));
        $this->assertTrue(is_array($definition->properties()));
        $this->assertTrue(is_string($definition->name()));
    }

    public function testAdapters()
    {
        $definition = new FbpDefinition();
        $definition->definition($this->getData());

        $this->assertTrue(is_string($definition->toFbp()));
        $this->assertTrue(is_string($definition->toJson()));
        $this->assertTrue(is_string($definition->toYaml()));
    }

    private function getData()
    {
        return [
            'properties' => ['name' => '',],
            'initializers' => [
                [
                    'data' => 'yadda',
                    'tgt' => [
                        'process' => 'ReadFile',
                        'port' => 'IN',
                    ],
                ],
            ],
            'processes' => [
                'ReadFile' => [
                    'component' => 'ReadFile',
                    'metadata' => [
                        'label' => 'ReadFile',
                    ],
                ],
                'SplitbyLines' => [
                    'component' => 'SplitStr',
                    'metadata' => [
                        'label' => 'SplitStr',
                    ],
                ],
                'Display' => [
                    'component' => 'Output',
                    'metadata' => [
                        'label' => 'Output',
                    ],
                ],
                'CountLines' => [
                    'component' => 'Counter',
                    'metadata' => [
                        'label' => 'Counter',
                    ],
                ]
            ],
            'connections' => [
                [
                    'src' => [
                        'process' => 'ReadFile',
                        'port' => 'OUT',
                    ],
                    'tgt' => [
                        'process' => 'SplitbyLines',
                        'port' => 'IN',
                    ],
                ],
                [
                    'src' => [
                        'process' => 'ReadFile',
                        'port' => 'ERROR',
                    ],
                    'tgt' => [
                        'process' => 'Display',
                        'port' => 'IN',
                    ],
                ],
                [
                    'src' => [
                        'process' => 'SplitbyLines',
                        'port' => 'OUT',
                    ],
                    'tgt' => [
                        'process' => 'CountLines',
                        'port' => 'IN',
                    ],
                ],
                [
                    'src' => [
                        'process' => 'CountLines',
                        'port' => 'COUNT',
                    ],
                    'tgt' => [
                        'process' => 'Display',
                        'port' => 'IN',
                    ],
                ],
            ],
        ];
    }
}
