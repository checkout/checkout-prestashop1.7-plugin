<?php

namespace CheckoutCom\PrestaShop\Helpers;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;


class Debug
{
    /**
     * Name of the target file.
     *
     * @var string
     */
    const FILENAME = 'checkoutcom.log';

    /**
     * Path of the target file.
     *
     * @var string
     */
    const PATH = _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;

    /**
     * Append to log file.
     *
     * @param mixed $data The data
     */
    public static function write($data)
    {
        file_put_contents(static::PATH . static::FILENAME, print_r($data, 1) . "\n", FILE_APPEND);
    }
    
    /**
     * @param checkoutcom $module
     * @param string  $name
     * @param bool    $logsEnabled
     */
    public static function initLogger($module, $name = 'module', $logsEnabled = true)
    {
        $module->logger = new Logger($name);
        $level = $logsEnabled ? Logger::DEBUG : Logger::INFO;
        $fileHandler = new RotatingFileHandler(
            $module->getLocalPath().sprintf('logs/%s.log', self::hash(_PS_MODULE_DIR_)),
            3,
            $level
        );
        $fileHandler->setFilenameFormat('{date}_{filename}', 'Ym');
        $module->logger->pushHandler($fileHandler);
    }
    
    /**
     * @param string $value
     * @return string
     */
    public static function hash($value)
    {
        return md5(_COOKIE_IV_.$value);
    }

}
