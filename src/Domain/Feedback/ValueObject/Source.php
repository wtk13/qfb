<?php

declare(strict_types=1);

namespace Domain\Feedback\ValueObject;

enum Source: string
{
    case Email = 'email';
    case QrCode = 'qr_code';
}
