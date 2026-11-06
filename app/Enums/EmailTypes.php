<?php

namespace App\Enums;

enum EmailTypes: string
{
    case VERIFY_EMAIL = 'verify_email';
    case RESET_PASSWORD = 'reset_password';
    case ORDER_SUCCESS = 'order_success';
    case CREATION_SOLD = 'creation_sold';
    case CONTACT_EMAIL = 'contact_email';
    case INVITATION_EMAIL = 'invitation_email';
}
