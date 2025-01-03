<?php

namespace App\Models\Traits;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

trait LoggerTrait
{
    protected ?Logger $logger = null;
    protected bool $saveLog = true;

    public function initLogger(string $label): void
    {
        if ($this->saveLog) {
            $output = "%level_name% | %datetime% > %message% | %context% %extra%\n";
            $dateFormat = "Y-n-j, g:i a";

            $formatter = new LineFormatter(
                $output, // Format of message in log
                $dateFormat, // Datetime format
                true, // allowInlineLineBreaks option, default false
                true  // discard empty Square brackets in the end, default false
            );
            $this->logger = new Logger($this->table ?? 'alt-' . ' Logger');

            $stream_handler = new StreamHandler(__DIR__ . "../../../monolog/{$label}.log", Level::Debug);
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
    protected function log(string $level, string $message, array $context = []): void
    {
        if ($this->saveLog && $this->logger) {
            $this->logger->log($level, $message, $context);
        }

    }

    protected function logInfo(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }
}
