<?php declare(strict_types=1);

namespace Kunstmaan\UtilitiesBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Events;
use Doctrine\DBAL\Platforms\MySQL57Platform;
use Kunstmaan\UtilitiesBundle\Exception\InvalidSqlModeConfigurationException;

class Mysql57SqlModeFixEventSubscriber implements EventSubscriber
{
    const MODE_ALERT = 'alert';
    const MODE_OFF = 'off';
    const MODE_ON = 'on';

    const SQL_MODE_NAME = 'only_full_group_by';

    /**
     * @var string
     */
    private $mode;

    /**
     * Mysql57SqlModeFixEventSubscriber constructor.
     *
     * @param string $mode
     */
    public function __construct(string $mode)
    {
        $this->mode = $mode;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postConnect,
        ];
    }

    /**
     * @param ConnectionEventArgs $eventArgs
     *
     * @throws DBALException
     * @throws InvalidSqlModeConfigurationException
     */
    public function postConnect(ConnectionEventArgs $eventArgs): void
    {
        if (self::MODE_OFF != $this->mode && $this->isMySQL57Platform($eventArgs)) {
            $currentMode = $this->getSqlModeSqlVariable($eventArgs);
            if ($this->isOnlyFullGroupBy($currentMode)) {
                switch ($this->mode) {
                    case self::MODE_ALERT:
                        throw new InvalidSqlModeConfigurationException(
                            $currentMode,
                            ''
                        );
                    case self::MODE_ON:
                        $newMode = $this->cleanSqlMode($currentMode);
                        break;
                    default:
                        $newMode = $this->mode;
                }

                $this->setSqlMode($newMode, $eventArgs);
            }
        }
    }

    /**
     * @param ConnectionEventArgs $eventArgs
     *
     * @return bool
     *
     * @throws DBALException
     */
    private function isMySQL57Platform(ConnectionEventArgs $eventArgs): bool
    {
        return $eventArgs->getConnection()->getDatabasePlatform() instanceof MySQL57Platform;
    }

    /**
     * @param ConnectionEventArgs $eventArgs
     *
     * @return string
     *
     * @throws DBALException
     */
    private function getSqlModeSqlVariable(ConnectionEventArgs $eventArgs): string
    {
        $db = $eventArgs->getConnection();
        $stmt = $db->executeQuery('SHOW VARIABLES LIKE "sql_mode"');

        return (string) $stmt->fetchColumn(1) ?: '';
    }

    /**
     * @param string              $newMode
     * @param ConnectionEventArgs $eventArgs
     *
     * @throws DBALException
     */
    private function setSqlMode(string $newMode, ConnectionEventArgs $eventArgs): void
    {
        $db = $eventArgs->getConnection();
        $db->executeQuery('SET SESSION sql_mode = :mode', ['mode' => $newMode]);
    }

    /**
     * @param string $mode
     *
     * @return bool
     */
    private function isOnlyFullGroupBy(string $mode): bool
    {
        return stripos($mode, self::SQL_MODE_NAME) !== false;
    }

    /**
     * @param string $currentMode
     *
     * @return string
     */
    private function cleanSqlMode(string $currentMode): string
    {
        $modes = explode(',', $currentMode);
        $remove = [
            self::SQL_MODE_NAME,
        ];
        $newModes = array_udiff($modes, $remove, 'strcasecmp');

        return implode(',', $newModes);
    }
}
