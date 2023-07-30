<?php

function writeToLog($val, $name, $fileName = null)
{
    $statusLog = 1;
    if ($statusLog > 0) {
        $file = $fileName != null ? $fileName : 'test.log';
        if (@file_exists($file)) {
            $size = @filesize($file);
            if ($size > 2500 * 1024) {
                @unlink($file);
            }
        }
        $data   = date('Y-m-d H:i:s');
        $result = "\n[$name ($data)]\n".print_r($val, true);
        file_put_contents($file, $result."\n##########################################\n", FILE_APPEND);
    }
}