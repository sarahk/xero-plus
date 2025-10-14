<?php
declare(strict_types=1);

namespace App\Models\Traits;

use App\Classes\StorageClass;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

trait LoggerTrait
{
    protected ?Logger $logger = null;
    protected bool $saveLog = true;

    protected string $created_prefix = '# created_at: ';

    public function initLogger(string $label): void
    {
        if ($this->saveLog) {
            $tz = new DateTimeZone('Pacific/Auckland');
            $day_name = (new DateTimeImmutable('now', $tz))->format('l');
            $log_path = dirname(__DIR__, 3) . "/monolog/{$day_name}/";
            // maybe should be using DIRECTORY_SEPARATOR
            $this_log = $this->table ?? get_class($this) ?? static::class;
            //error_log('Setting log path to ' . $log_path . ' for ' . $this_log);

            $this->cleanOldLogsByDate($log_path, $tz);

            // Sanitize filename from label
            $fileBase = preg_replace('/[^A-Za-z0-9._-]+/', '_', $label) ?: 'app';
            $targetFile = $log_path . $fileBase . '.log';

            // Ensure header first so it’s at line #1
            $this->ensure_created_header($targetFile, $tz);


            $output = '%level_name% | %datetime% > %message% | %context% %extra%' . PHP_EOL;
            $date_format = 'Y-n-j, g:i a';

            $formatter = new \Monolog\Formatter\LineFormatter(
                $output, // Format of message in log
                $date_format, // Datetime format
                true, // allowInlineLineBreaks option, default false
                true  // discard empty Square brackets in the end, default false
            );
            $this->logger = new \Monolog\Logger($this_log . ' Logger');


            $stream_handler = new \Monolog\Handler\StreamHandler($targetFile, \Monolog\Level::Debug);
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

    /**
     * @throws \Exception
     */
    private function cleanOldLogsByDate(string $logPath, $tz): int
    {
        // Normalize path
        $logPath = rtrim($logPath, DIRECTORY_SEPARATOR);

        if (!is_dir($logPath) && !mkdir($logPath, 0775, true) && !is_dir($logPath)) {
            throw new \RuntimeException("Failed to create log path: $logPath");
        }

        // 0) Only once per calendar day
        $storage = new StorageClass(false);
        $today = (new DateTime('today'))->format('Y-m-d');
        $lastRun = $storage->getMonologLastCleanupDate();

        if ($lastRun === $today) {
            //return 0; // already cleaned today
        }

        // 2) Find files safely (glob can return false)
        $files = glob($logPath . '/*') ?: [];
        if (!$files) {
            $storage->setMonologLastCleanupDate($today);
            return 0;
        }

        $start_of_today = (new DateTimeImmutable('today', $tz))->getTimestamp();  // NZ midnight
        $deleted = 0;
        $eligible = 0;

        foreach ($files as $file) {
            if (!is_file($file)) continue;

            $created_timestamp = $this->read_created_at($file);

            // Treat missing header as "old" so we fix it
            if (!$created_timestamp || $created_timestamp < $start_of_today) {
                if (is_writable($file)) {
                    $eligible++;
                    // Remove and recreate with fresh header
                    if (@unlink($file)) {
                        $deleted++;
                        $this->ensure_created_header($file, $tz);
                    }
                }
            }
        }
        //error_log("cleanOldLogsByDate: deleted $deleted/$eligible files");
        $storage->setMonologLastCleanupDate($today);
        return $deleted;
    }

    /** Return the created-at timestamp from the header, or null if not present. */
    protected function read_created_at(string $path): ?int
    {
        if (!is_file($path)) return null;

        $fh = @fopen($path, 'rb');
        if (!$fh) return null;

        // Read just the first line
        $line = fgets($fh, 4096) ?: '';
        fclose($fh);

        if (str_starts_with($line, $this->created_prefix)) {
            $iso = trim(substr($line, strlen($this->created_prefix)));;
            $ts = strtotime($iso);
            return $ts ?: null;
        }
        return null;
    }

    /**
     * @throws \Exception
     */
    protected /**
     * Ensure the file exists and has a created_at header on the first line.
     * Returns the created-at timestamp (existing or newly written).
     *
     * If the file exists without a header, we prepend one (by rewriting via a temp file).
     */
    function ensure_created_header(string $path, ?DateTimeZone $tz = null): int
    {
        $tz ??= new DateTimeZone('Pacific/Auckland');

        // If already present, just read it.
        $existing = $this->read_created_at($path);
        if ($existing !== null) {
            return $existing;
        }

        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException("Failed to create directory: $dir");
        }

        $nowIso = (new DateTimeImmutable('now', $tz))->format(DATE_ATOM);
        $header = $this->created_prefix . $nowIso . PHP_EOL;

        if (!file_exists($path) || filesize($path) === 0) {
            // Brand new or empty file: just write the header.
            $ok = @file_put_contents($path, $header, LOCK_EX);
            if ($ok === false) {
                throw new RuntimeException("Failed to write header to: $path");
            }
            return strtotime($nowIso) ?: time();
        }
        // File exists without header: rewrite with header + old contents.
        $tmp = $path . '.tmp.' . bin2hex(random_bytes(6));
        $in = @fopen($path, 'rb');
        $out = @fopen($tmp, 'wb');
        if (!$in || !$out) {
            @fclose($in);
            @fclose($out);
            throw new RuntimeException("Failed to open files to prepend header");
        }

        // Write header, then copy original contents
        fwrite($out, $header);
        stream_copy_to_stream($in, $out);
        fclose($in);
        fflush($out);
        fclose($out);

        // Try to carry over permissions
        @chmod($tmp, fileperms($path) & 0777);

        // Atomic replace on same filesystem
        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            throw new RuntimeException("Failed to replace file with header: $path");
        }

        return strtotime($nowIso) ?: time();
    }
}
