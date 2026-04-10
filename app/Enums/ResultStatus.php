<?php

namespace App\Enums;

enum ResultStatus: string
{
    case None = 'none'; // nenhum
    case Pending = 'pending'; // pendente
    case Confirmed = 'confirmed'; // confirmado
    case Disputed = 'disputed'; // contestado
}
