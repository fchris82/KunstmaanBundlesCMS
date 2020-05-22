<?php

namespace Kunstmaan\UtilitiesBundle\Tests\DependencyInjection;

use Kunstmaan\UtilitiesBundle\DependencyInjection\Configuration;
use Kunstmaan\UtilitiesBundle\EventListener\Mysql57SqlModeFixEventSubscriber;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigurationTest
 */
class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    /**
     * @return \Symfony\Component\Config\Definition\ConfigurationInterface
     */
    protected function getConfiguration()
    {
        return new Configuration();
    }

    public function testProcessedValueContainsRequiredValue()
    {
        $array = [
            'cipher' => ['secret' => '%kernel.secret%'],
            'mysql57_sql_mode_fix' => Mysql57SqlModeFixEventSubscriber::MODE_ALERT,
        ];

        $this->assertProcessedConfigurationEquals([$array], $array);
    }

    /**
     * @param $set
     * @param $result
     *
     * @dataProvider dpTestProcessedMysql57SqlModeFixValue
     */
    public function testProcessedMysql57SqlModeFixValue($set, $result)
    {
        $config = [
            'cipher' => ['secret' => '%kernel.secret%'],
            'mysql57_sql_mode_fix' => $set,
        ];
        $result = [
            'cipher' => ['secret' => '%kernel.secret%'],
            'mysql57_sql_mode_fix' => $result,
        ];

        $this->assertProcessedConfigurationEquals([$config], $result);
    }

    public function dpTestProcessedMysql57SqlModeFixValue()
    {
        return [
            [null, Mysql57SqlModeFixEventSubscriber::MODE_ALERT],
            ['', Mysql57SqlModeFixEventSubscriber::MODE_ALERT],

            [false, Mysql57SqlModeFixEventSubscriber::MODE_OFF],
            [0, Mysql57SqlModeFixEventSubscriber::MODE_OFF],
            ['0', Mysql57SqlModeFixEventSubscriber::MODE_OFF],

            [true, Mysql57SqlModeFixEventSubscriber::MODE_ON],
            [1, Mysql57SqlModeFixEventSubscriber::MODE_ON],
            ['1', Mysql57SqlModeFixEventSubscriber::MODE_ON],

            [
                'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION',
                'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION',
            ]
        ];
    }
}
