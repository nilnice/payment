<?php

namespace Nilnice\Payment;

use Illuminate\Config\Repository;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

trait LogTrait
{
    /**
     * @param \Illuminate\Config\Repository $repository
     * @param string                        $name
     *
     * @throws \Exception
     */
    public function registerLogger(Repository $repository, string $name) : void
    {
        $handler = new StreamHandler(
            $repository->get('log.file'),
            $repository->get('log.level', Logger::WARNING)
        );
        $formatter = new LineFormatter();
        $handler->setFormatter($formatter);

        $logger = new Logger($name);
        $logger->pushHandler($handler);
        Log::setLogger($logger);
    }
}
