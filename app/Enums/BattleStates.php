<?php

namespace App\Enums;

enum BattleStates: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case FINISHED = 'finished';
}
