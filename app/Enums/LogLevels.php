<?php

namespace App\Enums;

/**
 * https://www.rfc-editor.org/rfc/rfc5424#page-36
 */
enum LogLevels: string
{
    case EMERGENCY = 'emergency'; // 0 [system is unusable]
    case ALERT = 'alert'; // 1 [action must be taken immediately]
    case CRITICAL = 'critical'; // 2 [critical conditions]
    case ERROR = 'error'; // 3 [error conditions]
    case WARNING = 'warning'; // 4 [warning conditions]
    case NOTICE = 'notice'; // 5 [normal but significant condition]
    case INFO = 'informational'; // 6 [informational messages]
    case DEBUG = 'debug'; // 7 [debug-level messages]
}
