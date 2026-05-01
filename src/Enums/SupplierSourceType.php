<?php

namespace Platform\Commerce\Enums;

enum SupplierSourceType: string
{
    case Manual = 'manual';
    case WebhookPost = 'webhook_post';
    case PullGet = 'pull_get';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manuell',
            self::WebhookPost => 'Webhook (POST)',
            self::PullGet => 'Pull (GET)',
        };
    }
}
