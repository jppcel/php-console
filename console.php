<?php

class Console
{

    private static $depth = 0;

    private static function wrap_script($script)
    {
        return "<script type='text/javascript'>" . $script . "</script>";
    }

    private static function get_indent()
    {
        return str_repeat("   ", Console::$depth);
    }

    private static function format_by_type($input)
    {
        $type = gettype($input);
        switch ($type) {
            case "array":
                Console::$depth++;
                $contents = "Array(";
                if (Console::$depth === 1) {
                    $contents = "\\n" . $contents;
                }
                foreach ($input as $key => $element) {
                    $contents .= "\\n" . Console::get_indent() . "[" . $key . "] => ";
                    $contents .= Console::format_by_type($element);
                    if ($key !== count($input) - 1) {
                        $contents .= ", ";
                    }
                }
                Console::$depth--;
                $contents .= "\\n" . Console::get_indent() . ")";
                return $contents;
                break;
            case "object":
                Console::$depth++;
                $contents = get_class($input) . " Object {";
                if (Console::$depth === 1) {
                    $contents = "\\n" . $contents;
                }
                $input_keys = get_object_vars($input);
                foreach ($input_keys as $key => $value) {
                    $contents .= "\\n" . Console::get_indent() . "[" . $key . "] => ";
                    $contents .= Console::format_by_type($value);
                }
                Console::$depth--;
                $contents .= "\\n" . Console::get_indent() . "}";
                return $contents;
                break;
            default:
                return $input;
        }
    }

    private static function printLog($input, $bt, $protocol)
    {
        // styling
        $console_start = "console.$protocol('%c";
        $console_end = "', 'color:white;background-color:black;padding:0 .25em;margin-right:.5em;', '');";

        // file name and line number
        $caller = array_shift($bt);
        $file = $caller["file"];
        $file = substr($file, strrpos($file, "\\") + 1);
        $line = $caller["line"];
        $meta = $file . ":" . $line;

        // contents to log
        $contents = str_replace("'", "\'", $input);
        $contents = Console::format_by_type($contents);

        Console::$depth = 0;

        echo Console::wrap_script($console_start . $meta . "%c" . $contents . $console_end);
    }

    public static function log($input)
    {
        Console::printLog($input, debug_backtrace(), "log");
    }
    public static function info($input)
    {
        Console::printLog($input, debug_backtrace(), "info");
    }
    public static function warn($input)
    {
        Console::printLog($input, debug_backtrace(), "warn");
    }
    public static function error($input)
    {
        Console::printLog($input, debug_backtrace(), "error");
    }
    public static function clear()
    {
        echo Console::wrap_script("console.clear()");
    }

}
