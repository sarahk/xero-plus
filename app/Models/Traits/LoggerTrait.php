<?php

namespace App\Models\Traits;

use App\StorageClass;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use DateTime;

trait LoggerTrait
{
    protected ?Logger $logger = null;
    protected bool $saveLog = true;

    public function initLogger(string $label): void
    {
        if ($this->saveLog) {
            $day_name = date('l');
            $log_path = __DIR__ . "/../../../monolog/{$day_name}/";

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

    function cleanOldLogsByDate(string $logPath): int
    {
        // Normalize path
        $logPath = rtrim($logPath, DIRECTORY_SEPARATOR);

        if (!is_dir($logPath)) {
            mkdir($logPath, 0775, true);
        }

        // 0) Only once per calendar day
        $storage = new StorageClass();
        $today = (new DateTime('today'))->format('Y-m-d');
        $lastRun = $storage->getMonologLastCleanupDate();

        if ($lastRun === $today) {
            //return 0; // already cleaned today
        }

        // 1) Folder must exist
        if (!is_dir($logPath)) {
            $storage->setMonologLastCleanupDate($today); // avoid trying again this request
            return 0;
        }

        // 2) Find files safely (glob can return false)
        $files = glob($logPath . '/*') ?: [];
        if (!$files) {
            $storage->setMonologLastCleanupDate($today);
            return 0;
        }

        $startOfToday = strtotime('today');
        $deleted = 0;

        foreach ($files as $file) {
            if (!is_file($file)) continue;

            $mtime = @filemtime($file) ?: 0;
            if ($mtime < $startOfToday) {
                // Optional: check writability, helpful on macOS if owner/perm differ
                if (is_writable($file)) {
                    if (@unlink($file)) {
                        $deleted++;
                    }
                }
            }
        }

        $storage->setMonologLastCleanupDate($today);
        return $deleted;
    }
}
