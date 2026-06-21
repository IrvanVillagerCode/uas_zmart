<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Indicates if the model should be bootstrapped with timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'full_name',
        'role',
        'avatar',
        'google_uid',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // We do not cast password to hashed since Firebase handles the credential checks
        ];
    }

    /**
     * Override remember token methods to disable database updates since the column is not present.
     * Must return null (not empty string) so Laravel skips the remember_token UPDATE query entirely.
     */
    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
        // Do nothing — no remember_token column in this table
    }

    public function getRememberTokenName()
    {
        return null; // Returning null tells Laravel to skip remember_token DB update
    }
}
