<?php

namespace App\Enums;

enum AspectRatios: string
{
    case TV_1_1 = 'tv_1_1';
    case TV_21_9 = 'tv_21_9';
    case TV_4_3 = 'tv_4_3';
    case WIDE_16_9 = 'wide_16_9';
    case WIDE_16_10 = 'wide_16_10';
    case MOBILE_4_5 = 'mobile_4_5';
    case MOBILE_2_3 = 'mobile_2_3';
    case MOBILE_9_16 = 'mobile_9_16';
}
