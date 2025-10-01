<?php

namespace App\Models\Enums;
enum TaskStatus: string
{
    use EnumHelper;

    case Active = 'active';
    case Hold = 'hold';
    case Scheduled = 'scheduled';
    case Done = 'done';
    case Cancelled = 'cancelled';


    public static function getLabel(string $val): string
    {
        $pointer = self::from($val);
        return match ($pointer) {
            self::Active => 'Active',
            self::Hold => 'On Hold',
            self::Scheduled => 'Scheduled',
            self::Cancelled => 'Cancelled',
            self::Done => 'Done',
            
        };
    }

    public static function getIcon(string $val): string
    {
        $taskStatus = self::from($val);
        return match ($taskStatus) {
            self::Active => 'play',
            self::Hold => 'pause',
            self::Scheduled => 'calendar',
            self::Cancelled => 'circle-xmark',
            self::Done => 'circle-check',
        };
    }

    public static function getTaskStatusBadge(string $val): string
    {
        $icon = self::getIcon($val);
        $label = self::getLabel($val);
        $first_letter = substr($label, 0, 1);
        $output = "<span class='badge badge-$val rounded-pill gap-1 px-2 py-1' title='$label'><i class='fa-solid fa-$icon'></i>$first_letter</span>";
        // $output = "<span class='badge badge-$val' title='$label'><i class='fa fa-$icon me-2'></i>$first_letter</span>";
        return $output;
    }

    private static function allowedNext(self $from): array
    {
        return match ($from) {
            self::Active, self::Scheduled => [self::Active, self::Hold, self::Scheduled, self::Cancelled, self::Done],
            self::Hold => [self::Active, self::Hold, self::Cancelled, self::Done],
            self::Cancelled => [self::Active, self::Cancelled],
            self::Done => [self::Done],
        };
    }

    public static function taskNeedsAttention(string $val): bool
    {
        $taskStatus = self::from($val);
        return match ($taskStatus) {
            self::Active, self::Hold, self::Scheduled => true,
            self::Done, self::Cancelled => false,
        };
    }

    private static function getButtonClass(self $val): string
    {
        return match ($val) {
            self::Active => 'btn-primary',
            self::Hold => 'btn-warning',
            self::Scheduled => 'btn-info',
            self::Cancelled => 'btn-danger',
            self::Done => 'btn-success',
        };
    }

    public static function getButtons($task_id, $task_status): string
    {
        $pointer = self::from($task_status);
        $buttons = self::allowedNext($pointer);
        $output = [];
        $output[] = "<div class='btn-group btn-group-sm' role='group' aria-label='task buttons'>";
        foreach ($buttons as $button) {

            if ($button !== $pointer && $button !== self::Scheduled) {
                $label = self::getLabel($button->value);
                $class = self::getButtonClass($button);
                $output[] = "<button href='' data-task_id='$task_id' data-status='{$button->value}' class='btn $class' data-mdb-ripple-init>$label</button>";
            }
        }
        $output[] = "</div>";
        return implode($output);
    }
}

