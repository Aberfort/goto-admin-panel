<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'reg_url',
        'front_url',
        'app_url',
        'api_token',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($website) {
            if (empty($website->api_token)) {
                do {
                    $token = Str::random(60);
                } while (Website::where('api_token', $token)->exists());

                $website->api_token = $token;
            }
        });
    }
}
