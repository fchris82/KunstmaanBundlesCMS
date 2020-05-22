<?php

namespace Kunstmaan\UtilitiesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class KunstmaanUtilitiesExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if ($container->hasParameter('kunstmaan_utilities.cipher.secret')) {
            @trigger_error('Setting the "kunstmaan_utilities.cipher.secret" parameter is deprecated since KunstmaanUtilitiesBundle 5.2, this value will be ignored/overwritten in KunstmaanUtilitiesBundle 6.0. Use the "kunstmaan_utilities.cipher.secret" config instead if you want to set a different value than the default "%kernel.secret%".', E_USER_DEPRECATED);
        } elseif ($container->hasParameter('secret')) {
            $container->setParameter('kunstmaan_utilities.cipher.secret', $container->getParameter('secret'));
        } else {
            $container->setParameter('kunstmaan_utilities.cipher.secret', $config['cipher']['secret']);
        }

        if ($container->hasParameter('kunstmaan_utilities.mysql57_sql_mode_fix.mode.config')) {
            throw new InvalidConfigurationException('Don\'t use the `%s` parameter directly, use the `%s` config!');
        } else {
            $container->setParameter('kunstmaan_utilities.mysql57_sql_mode_fix.mode.config', $config['mysql57_sql_mode_fix']);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('commands.yml');
        $loader->load('services.yml');
    }
}
