<?php

namespace App\Models;

use App\Enums\ExpertKycStatus;
use App\Notifications\Expert\ResetPasswordNotification;
use App\Notifications\Expert\VerifyEmailNotification;
use Database\Factories\ExpertFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'is_active', 'kyc_status'])]
#[Hidden(['password', 'remember_token'])]
class Expert extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<ExpertFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ACCESS_ABILITY = 'expert:access';

    /**
     * Normalize email addresses for all Eloquent writes.
     *
     * @return Attribute<string, string>
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => mb_strtolower(trim($value)),
        );
    }

    /**
     * Send the expert-specific verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Send the expert-specific password reset notification.
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'kyc_status' => ExpertKycStatus::class,
            'password' => 'hashed',
        ];
    }
}
