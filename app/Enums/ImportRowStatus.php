<?php

namespace App\Enums;

enum ImportRowStatus: string
{
    case Ready = 'ready';
    case Update = 'update';
    case Conflict = 'conflict';
    case Invalid = 'invalid';
    case Imported = 'imported';
    case Ignored = 'ignored';
}
