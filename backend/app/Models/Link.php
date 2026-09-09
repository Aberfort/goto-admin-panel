<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Link extends Model
{
    use HasFactory;

    protected $fillable = [
        'target_url',
        'short_code',
        'is_active',
        'expires_at',
    ];

    /**
     * The hash never leaves the server. The frontend gets has_password
     * instead, which is all it needs to render the right controls.
     */
    protected $hidden = [
        'password',
    ];

    protected $appends = [
        'has_password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Link $link) {
            if (empty($link->short_code)) {
                do {
                    $code = Str::random(7);
                } while (static::where('short_code', $code)->exists());

                $link->short_code = $code;
            }
        });
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isPasswordProtected(): bool
    {
        return $this->password !== null;
    }

    /** Whether a redirect should happen at all, ignoring the password gate. */
    public function isReachable(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }

    protected function hasPassword(): Attribute
    {
        return Attribute::get(fn (): bool => $this->isPasswordProtected());
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }
}
