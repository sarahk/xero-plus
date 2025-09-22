<?php

namespace Tests\Integration;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

class TestLogger
{
    protected ?Logger $logger = null;
    protected bool $saveLog = true;
    public string $log_path = '';


    public function __construct()
    {
        if ($this->saveLog) {
            $day_name = date('l');
            $this->log_path = __DIR__ . "/../../app/monolog/{$day_name}/";
            if (!is_dir($this->log_path)) {
                mkdir($this->log_path, 0775, true);
            }

            $output = "%level_name% | %datetime% > %message% | %context% %extra%\n";
            $date_format = "Y-n-j, g:i a";

            $formatter = new LineFormatter(
                $output, // Format of message in log
                $date_format, // Datetime format
                true, // allowInlineLineBreaks option, default false
                true  // discard empty Square brackets in the end, default false
            );
            $this->logger = new Logger('Test_Logger');

            $stream_handler = new StreamHandler("{$this->log_path}pest-tests.log", Level::Debug);
            $stream_handler->setFormatter($formatter);
            $this->logger->pushHandler($stream_handler);
            //$this->log('info', 'Logger Started', []);

        }
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context <mixed>
     * @return void
     */
    public function log(string $level, string $message, array $context = []): void
    {
        if ($this->saveLog && $this->logger) {
            $this->logger->log($level, $message, $context);
        }

    }
}