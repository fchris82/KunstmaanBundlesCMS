<?php declare(strict_types=1);

namespace Kunstmaan\UtilitiesBundle\Exception;

use Throwable;

class InvalidSqlModeConfigurationException extends \Exception
{
    /**
     * @var string
     */
    protected $currentMode;

    public function __construct($currentMode = "", $message = "", $code = 0, Throwable $previous = null)
    {
        $this->currentMode = $currentMode;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getCurrentMode(): string
    {
        return $this->currentMode;
    }
}
