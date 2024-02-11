<?php

declare(strict_types=1);

namespace ParadiseSecurity\Bundle\StateMachineBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class ParadiseSecurityStateMachineExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configPath = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'config']);
        $loader = new XmlFileLoader($container, new FileLocator($configPath));

        $configuration = $this->getConfiguration([], $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader->load('services.xml');

        $container->setParameter('paradise_security.state_machine.default_adapter', $config['default_adapter']);
        $container->setParameter('paradise_security.state_machine.graphs_to_adapters_mapping', $config['graphs_to_adapters_mapping']);
    }
}
