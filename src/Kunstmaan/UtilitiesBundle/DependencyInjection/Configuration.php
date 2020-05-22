<?php

namespace Kunstmaan\UtilitiesBundle\DependencyInjection;

use Kunstmaan\UtilitiesBundle\EventListener\Mysql57SqlModeFixEventSubscriber;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('kunstmaan_utilities');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('kunstmaan_utilities');
        }

        $rootNode
            ->children()
                ->arrayNode('cipher')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('secret')->defaultValue('%kernel.secret%')->end()
                    ->end()
                ->end()
                ->scalarNode('mysql57_sql_mode_fix')
                    ->info(
                        'The `only_full_group_by` `sql_mode` settings - which is set as default in MySQL 5.7 and' .
                        ' above - incompatible with Kunstmaan. You have 2 different solutions for this. 1. You change the' .
                        ' `sql_mode` settings in MySQL configuration either in `my.cnf` file or using `SET` sql command:' .
                        ' https://dev.mysql.com/doc/refman/5.7/en/sql-mode.html In that case you can change this' .
                        ' configuration settings to: `off`. 2. Otherwise you can set this configuration to `on` and Kunstmaan' .
                        ' will automatically disable this setting in its database connections - keeping the other `sql_mode`' .
                        ' settings. It is possible to set custom `sql_mode` settings here instead of `on`. In that case' .
                        ' Kunstmaan will use this. Possible values: alert, on, off, [custom and valid `sql_mode` setting]'
                    )
                    ->example('on')
                    ->defaultValue(Mysql57SqlModeFixEventSubscriber::MODE_ALERT)
                    ->beforeNormalization()
                        ->always(function ($v) {
                            switch (strtolower((string) trim($v))) {
                                case '':
                                    $v = $v === false
                                        ? Mysql57SqlModeFixEventSubscriber::MODE_OFF
                                        : Mysql57SqlModeFixEventSubscriber::MODE_ALERT;
                                    break;
                                case '0':
                                case 'disable':
                                    $v = Mysql57SqlModeFixEventSubscriber::MODE_OFF;
                                    break;
                                case '1':
                                case 'enable':
                                    $v = Mysql57SqlModeFixEventSubscriber::MODE_ON;
                                    break;
                            }

                            return $v;
                        })
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
