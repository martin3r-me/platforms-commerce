<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Platform\Commerce\Enums\SupplierSourceType;
use Platform\Commerce\Enums\SupplierStatus;

class CommerceSupplier extends Model
{
    use HasFactory, SoftDeletes, \Platform\ActivityLog\Traits\LogsActivity;

    protected $table = 'commerce_suppliers';

    protected $fillable = [
        'user_id',
        'team_id',
        'name',
        'description',
        'source_type',
        'endpoint_token',
        'pull_url',
        'pull_headers',
        'pull_schedule',
        'natural_key',
        'status',
        'metadata',
        'last_import_at',
    ];

    protected $casts = [
        'source_type' => SupplierSourceType::class,
        'status' => SupplierStatus::class,
        'metadata' => 'array',
        'pull_headers' => 'encrypted:array',
        'last_import_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $supplier) {
            if ($supplier->source_type === SupplierSourceType::WebhookPost && !$supplier->endpoint_token) {
                $supplier->endpoint_token = Str::random(64);
            }
        });
    }

    // --- Relationships ---

    public function articles()
    {
        return $this->belongsToMany(
            CommerceArticle::class,
            'commerce_article_supplier',
            'supplier_id',
            'article_id'
        )->withPivot([
            'id',
            'external_id',
            'purchase_price',
            'purchase_currency',
            'valid_from',
            'valid_until',
            'is_preferred',
            'last_synced_at',
        ])->withTimestamps();
    }

    public function fieldMappings()
    {
        return $this->hasMany(CommerceSupplierFieldMapping::class, 'commerce_supplier_id')
            ->orderBy('position');
    }

    public function imports()
    {
        return $this->hasMany(CommerceSupplierImport::class, 'commerce_supplier_id')
            ->orderByDesc('created_at');
    }

    public function batches()
    {
        return $this->hasMany(CommerceArticleBatch::class, 'commerce_supplier_id');
    }

    public function creator()
    {
        return $this->belongsTo(\Platform\Core\Models\User::class, 'user_id');
    }

    public function team()
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id');
    }

    // --- Helpers ---

    public function isOnboarding(): bool
    {
        return $this->status === SupplierStatus::Onboarding;
    }

    public function isActive(): bool
    {
        return $this->status === SupplierStatus::Active;
    }

    public function isWebhook(): bool
    {
        return $this->source_type === SupplierSourceType::WebhookPost;
    }

    public function isPull(): bool
    {
        return $this->source_type === SupplierSourceType::PullGet;
    }

    public function isManual(): bool
    {
        return $this->source_type === SupplierSourceType::Manual;
    }

    public function getSamplePayloadAttribute(): ?array
    {
        return $this->metadata['sample_payload'] ?? null;
    }
}
