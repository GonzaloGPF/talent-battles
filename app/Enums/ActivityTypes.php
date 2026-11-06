<?php

namespace App\Enums;

use App\Interfaces\HasColorsInterface;

enum ActivityTypes: string implements HasColorsInterface
{
    case NEW_TRACK = 'new_track';
    case NEW_DESIGN = 'new_design';
    case DO_COMMENT = 'do_comment';
    case DO_LIKE = 'do_like';
    case DO_FAVORITE = 'do_favorite';
    case DO_DOWNLOAD = 'do_download';
    case DO_BUY = 'do_buy';
    case DO_PROMO = 'do_promo';
    case WIN_BATTLE = 'win_battle';
    case DO_DONATION = 'do_donation';

    public function getColor(): string
    {
        return match ($this) {
            ActivityTypes::NEW_TRACK => '#1abc9c',
            ActivityTypes::NEW_DESIGN => '#1abc9c',
            ActivityTypes::DO_COMMENT => '#2ecc71',
            ActivityTypes::DO_LIKE => '#3498db',
            ActivityTypes::DO_FAVORITE => '#9b59b6',
            ActivityTypes::DO_DOWNLOAD => '#f1c40f',
            ActivityTypes::DO_BUY => '#e67e22',
            ActivityTypes::DO_PROMO => '#f39c12',
            ActivityTypes::WIN_BATTLE => '#d35400',
            ActivityTypes::DO_DONATION => '#d35400',
        };
    }
}
