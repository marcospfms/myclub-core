<?php

namespace App\Enums;

enum MatchConfirmation: string
{
    case Pending = 'pending'; // pendente
    case Confirmed = 'confirmed'; // confirmado
    case Rejected = 'rejected'; // recusado
    case Expired = 'expired'; // expirado
}
