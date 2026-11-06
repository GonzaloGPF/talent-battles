<?php

namespace App\Enums;

enum Features: string
{
    case ITS_FREE = 'its_free';
    case HAS_SAMPLE = 'has_sample';
    case HAS_HOOK = 'has_hook';
    case HAS_INTRO = 'has_intro';
    case HAS_ENDING = 'has_ending';
    case HAS_IMAGE = 'has_image';
}
