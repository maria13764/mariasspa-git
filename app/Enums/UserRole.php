<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Therapist = 'therapist';
    case Client = 'client';
}
