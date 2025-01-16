<?php

namespace App\Models\Traits;

use App\StorageClass;
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
            $day_name = date('l');
            $log_path = __DIR__ . "../../../monolog/{$day_name}/";

            $this->cleanOldLogsByDate($log_path);
            $output = "%level_name% | %datetime% > %message% | %context% %extra%\n";
            $date_format = "Y-n-j, g:i a";

            $formatter = new LineFormatter(
                $output, // Format of message in log
                $date_format, // Datetime format
                true, // allowInlineLineBreaks option, default false
                true  // discard empty Square brackets in the end, default false
            );
            $this->logger = new Logger($this->table ?? 'alt-' . ' Logger');


            $stream_handler = new StreamHandler("{$log_path}{$label}.log", Level::Debug);
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

    protected function logError(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    function cleanOldLogsByDate(string $log_Path): void
    {
        $storage = new StorageClass();
        $checked = $storage->getMonologCheckStatus();
        if ($checked === '1') {
            return;
        }
        // Ensure the folder exists
        if (!is_dir($log_Path)) {
            return;
        }

        // Get today's date (start of the day) as a timestamp
        $start_of_today = strtotime('today');

        // Iterate through all files in the folder
        $files = glob("$log_Path/*");
        foreach ($files as $file) {
            if (is_file($file)) {
                // Get the file's last modification time
                $file_mod_time = filemtime($file);

                // Check if the file's modification date is before today
                if ($file_mod_time < $start_of_today) {
                    unlink($file);
                }
            }
        }
        $storage->setMonologCheckStatus('1');
        unset($storage);
    }
}
