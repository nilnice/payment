<?php

namespace Nilnice\Payment;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * @method static Logger log($level, $message, array $context = [])
 * @method static Logger debug($message, array $context = [])
 * @method static Logger info($message, array $context = [])
 * @method static Logger notice($message, array $context = [])
 * @method static Logger warn($message, array $context = [])
 * @method static Logger warning($message, array $context = [])
 * @method static Logger err($message, array $context = [])
 * @method static Logger error($message, array $context = [])
 * @method static Logger crit($message, array $context = [])
 * @method static Logger critical($message, array $context = [])
 * @method static Logger alert($message, array $context = [])
 * @method static Logger emerg($message, array $context = [])
 * @method static Logger emergency($message, array $context = [])
 */
class Log
{
    /**
     * @var \Monolog\Logger
     */
    protected static $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public static function setLogger(LoggerInterface $logger) : void
    {
        self::$logger = $logger;
    }

    /**
     * @return \Monolog\Logger
     */
    public static function getLogger() : Logger
    {
        return self::$logger ?? self::$logger = self::getLoggerInstance();
    }

    /**
     * @param string $method
     * @param mixed  $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $method, $arguments)
    {
        $function = [self::getLogger(), $method];

        return \forward_static_call_array($function, $arguments);
    }

    /**
     * @param string $method
     * @param mixed  $arguments
     *
     * @return mixed
     */
    public function __call(string $method, $arguments)
    {
        $function = [self::getLogger(), $method];

        return \call_user_func_array($function, $arguments);
    }

    /**
     * @return \Monolog\Logger
     */
    protected static function getLoggerInstance() : Logger
    {
        $maxFiles = 7;
        $filename = sys_get_temp_dir() . '/logs/pay.log';
        $handler = new RotatingFileHandler($filename, $maxFiles);
        $handler->setFilenameFormat('{date}-{filename}', 'Y-m-d');
        $formatter = new LineFormatter(null, null, true, true);
        $handler->setFormatter($formatter);

        $logger = new Logger('monolog');
        $logger->pushHandler($handler);

        return $logger;
    }
}
