<?php

namespace App\Models\Traits;
trait DebugTrait
{
    protected ?bool $isJson = null;
    protected int $stackLevels = 5;
    protected bool $showObjectMethods = false;

    // TODO
    // different output format for debugging json files
    public function debug($val): void
    {
        if (is_null($this->isJson)) $this->getIsJson();
        // todo if it's json, format accordingly
        $bt = debug_backtrace();
        $caller = array_shift($bt);
        $caller2 = (count($bt) > 0) ? array_shift($bt) : false;


        if ($this->isJson) {
            echo '>' . PHP_EOL;
            $this->showJsonValue('', $val);
        } else {
            echo '<ul>';
            $this->showValue('', $val);
            echo '</ul>';
        }

        //$this->echoCaller($caller);
        //if ($caller2) $this->echoCaller($caller2);
        $loops = min(count($bt) - 1, $this->stackLevels);
        for ($i = 0; $i <= $loops; $i++) {
            $this->echoCaller($bt[$i]);
        }

        if ($this->isJson) echo "------------------------------------" . PHP_EOL;
        else echo "<hr>\n";
    }

    // show where debug was called from
    protected function echoCaller($caller): void
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

    protected function showValue($k, $val): void
    {
        $ul = "<ul style='list-style-type: disc; padding: 1em;'>";
        echo '<li>';
        if (is_array($val)) {
            echo $k . $ul;

            foreach ($val as $key => $row) {
                $this->showValue($key, $row);
            }
            echo '</ul></li>';
        } else if (is_object($val)) {
            echo $k . $ul;

            foreach ((array)$val as $key => $row) {
                $this->showValue($key, $row);
            }
            if ($this->showObjectMethods) {
                $methods = get_class_methods($val);
                foreach ($methods as $v) {
                    echo "<li><i>$v</i></li>";
                }
            }
            echo '</ul></li>';
        } else {
            echo(strlen($k) ? "$k: " : ''), is_string($val) ? '"' . $val . '"' : $val;
        }
        echo "</li>";
    }

    protected function showJsonValue($k, $val, $level = 1): void
    {
        $begin = str_repeat('>', $level + 1) . ' ';

        if (is_array($val)) {
            echo $begin . $k . PHP_EOL;

            foreach ($val as $key => $row) {
                $this->showJsonValue($key, $row, $level + 1);
            }

        } else if (is_object($val)) {
            echo $begin . $k . PHP_EOL;

            foreach ((array)$val as $key => $row) {
                $this->showJsonValue($key, $row, $level + 1);
            }
            if ($this->showObjectMethods) {
                $methods = get_class_methods($val);
                foreach ($methods as $v) {
                    echo $begin . $v . PHP_EOL;
                }
            }
        } else {
            echo $begin . (strlen($k) ? "$k: " : ''), is_string($val) ? '"' . $val . '"' : $val;
        }
        echo PHP_EOL;
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
