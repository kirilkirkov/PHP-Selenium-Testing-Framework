<?php

namespace Src;

/**
 * Class CmdMessages
 */
class CmdMessages
{
    public static function printMessage($message)
    {
        if(CMD_OUTPUT) {
            echo PHP_EOL . date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL;
        }
    }
}