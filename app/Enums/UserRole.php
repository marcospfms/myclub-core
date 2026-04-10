<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin'; // administrador
    case User = 'user'; // usuário
}
