<?php

declare(strict_types=1);

namespace ParadiseSecurity\Bundle\StateMachineBundle\Test\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use ParadiseSecurity\Bundle\StateMachineBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    public function testConfigureDefaultStateMachineAdapter(): void
    {
        $this->assertProcessedConfigurationEquals(
            [
                [
                    'default_adapter' => 'symfony_workflow',
                ],
            ],
            [
                'default_adapter' => 'symfony_workflow',
                'graphs_to_adapters_mapping' => [],
            ],
        );
    }

    public function testConfigureStateMachineAdaptersMapping(): void
    {
        $this->assertProcessedConfigurationEquals(
            [
                [
                    'graphs_to_adapters_mapping' => [
                        'order' => 'symfony_workflow',
                        'payment' => 'winzou_state_machine',
                    ],
                ],
            ],
            [
                'default_adapter' => 'winzou_state_machine',
                'graphs_to_adapters_mapping' => [
                    'order' => 'symfony_workflow',
                    'payment' => 'winzou_state_machine',
                ],
            ],
        );
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
