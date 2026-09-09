<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Domain extends Model
{
    use HasFactory;

    /** The DNS label the verification TXT record lives under. */
    public const TXT_PREFIX = '_linkfleet';

    protected $fillable = [
        'host',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    protected $appends = [
        'is_verified',
        'txt_record_name',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Domain $domain) {
            if (empty($domain->verification_token)) {
                $domain->verification_token = Str::random(40);
            }
        });
    }

    protected function isVerified(): Attribute
    {
        return Attribute::get(fn (): bool => $this->verified_at !== null);
    }

    /** Where the owner has to put the TXT record, spelled out for the UI. */
    protected function txtRecordName(): Attribute
    {
        return Attribute::get(fn (): string => self::TXT_PREFIX.'.'.$this->host);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
