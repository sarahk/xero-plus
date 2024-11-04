<?php

namespace App\Models\Traits;
trait DebugTrait
{
    protected ?bool $isJson = null;
    protected int $stackLevels = 5;
    protected bool $showObjectMethods = false;

    public function debug(mixed $val): void
    {
        if (is_null($this->isJson)) $this->getIsJson();

        $bt = debug_backtrace();

        if ($this->isJson) {
            echo '>' . PHP_EOL;
        } else {
            echo '<ul>';
        }

        $this->showValue('', $val);

        if (!$this->isJson) {
            echo '</ul>';
        }


        $loops = min(count($bt) - 1, $this->stackLevels);
        for ($i = 0; $i <= $loops; $i++) {
            $this->echoCaller($bt[$i]);
        }

        if ($this->isJson) echo "------------------------------------" . PHP_EOL;
        else echo "<hr>\n";
    }

    // show where debug was called from

    /**
     * @param array $caller <int, array<string,string>>
     * @return void
     */
    protected function echoCaller(array $caller): void
    {
        $line1 = "{$caller['file']} Line {$caller['line']}";
        $line2 = "{$caller['class']}{$caller['type']}{$caller['function']}";

        if ($this->isJson) {
            echo "> $line1" . PHP_EOL;
            echo "  $line2" . PHP_EOL;
        } else {
            echo "<ul>
            <li>$line1<br/>
            $line2</li>
        </ul>
        ";
        }

        // could also export
        // ["args"]=>["ae75d056-4af7-484d-b709-94439130faa4", "7fee9f9a-98ff-40cb-8568-90f95de7d94b"];
    }


    protected function showValue(mixed $k, mixed $val, int $level = 1): void
    {
        $ul = ($this->isJson ? PHP_EOL : "<ul style='list-style-type: disc; padding: 1em;'>");
        $begin = ($this->isJson) ? str_repeat('>', $level + 1) . ' ' : '<li>';

        $format = ($this->isJson
                ? str_repeat('>', $level + 1) . "%s %s: %s"
                : "<li style='list-style-type: %s'>%s: %s</li>") . PHP_EOL;

        $variableType = gettype($val);

        switch ($variableType) {
            case "boolean":
                $boolean = ($val ? 'True' : 'False');
                printf($format, '', $k, $boolean);
                break;

            case  "integer":
            case  "double" :
                printf($format, ($this->isJson) ? '' : 'circle', $k, "$val");
                break;

            case "string":
                printf($format, ($this->isJson) ? '' : 'disc', $k, "\"$val\"");
                break;

            case "array":

                echo $begin . '[array]' . $k . $ul;

                if (count($val))
                    foreach ($val as $key => $row) {
                        $this->showValue($key, $row, $level + 1);
                    }
                else $this->showValue(0, '[]', $level + 1);

                echo($this->isJson ? '' : '</ul></li>');
                break;

            case "object":
                echo $begin . '[object]' . $k . $ul;

                foreach ((array)$val as $key => $row) {
                    $this->showValue($key, $row, $level + 1);
                }
                if ($this->showObjectMethods) {
                    $methods = get_class_methods($val);
                    foreach ($methods as $v) {
                        printf($format, 'square', '', "<i>$v</i>");
                    }
                }
                if (!$this->isJson) echo '</ul></li>';
                break;

            case "NULL":
                printf($format, '', $k, 'NULL');
                //echo $begin . (strlen($k) ? "$k: " : '') . 'NULL';
                break;

            case "resource":
            case "resource (closed)":
            case "unknown type":
            default:
                echo $begin . (strlen("$k") ? "$k: " : '') . $variableType;
        }

        echo($this->isJson ? PHP_EOL : "</li>");
    }


    protected function getIsJson(): void
    {
        $headers = headers_list();

        foreach ($headers as $header) {
            if (stripos($header, 'application/json') !== false) {
                $this->isJson = true;
                return;
            }
        }
    }
}
