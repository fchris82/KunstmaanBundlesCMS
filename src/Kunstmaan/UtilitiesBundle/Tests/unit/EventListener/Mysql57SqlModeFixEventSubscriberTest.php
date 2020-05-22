<?php

namespace Kunstmaan\UtilitiesBundle\Tests\EventListener;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Platforms\MySQL57Platform;
use Kunstmaan\UtilitiesBundle\EventListener\Mysql57SqlModeFixEventSubscriber;
use PHPUnit\Framework\TestCase;

class Mysql57SqlModeFixEventSubscriberTest extends TestCase
{
    /**
     * @param string      $configMode
     * @param string      $platformClass
     * @param string      $sqlMode
     * @param string|null $result
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Kunstmaan\UtilitiesBundle\Exception\InvalidSqlModeConfigurationException
     *
     * @dataProvider dpTestPostConnect
     */
    public function testPostConnect($configMode, $platformClass, $sqlMode, $result)
    {
        $subscriber = new Mysql57SqlModeFixEventSubscriber($configMode);
        $connectionMock = $this->createMock(Connection::class);

        $connectionMock
            ->method('getDatabasePlatform')
            ->willReturn(new $platformClass);

        $stmtMock = $this->createMock(ResultStatement::class)
            ->method('fetchColumn')
            ->willReturn($sqlMode);
        $connectionMock
            ->method('executeQuery')
            ->with($this->equalTo('SHOW VARIABLES LIKE "sql_mode"'))
            ->willReturn($stmtMock);
        $connectionMock
            ->expects($this->exactly($result ? 1 : 0))
            ->method('executeQuery')
            ->with($this->equalTo('SET SESSION sql_mode = :mode'))
            ->will($this->returnCallback(function ($query, $newValue) use ($result) {
                $this->assertEquals($result, $newValue);
            }));

        $event = new ConnectionEventArgs($connectionMock);
        $subscriber->postConnect($event);
    }

    public function dpTestPostConnect()
    {
        return [
            [Mysql57SqlModeFixEventSubscriber::MODE_OFF, MySQL57Platform::class, null, null],
        ];
    }
}
