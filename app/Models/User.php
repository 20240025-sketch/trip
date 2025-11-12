<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['is_admin'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the is_admin attribute.
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user is admin
     * Admin: email does NOT start with a digit (e.g., thjthj@seiei.ac.jp)
     */
    public function isAdmin(): bool
    {
        // If email starts with a digit, it's a regular user, not admin
        return preg_match('/^\d/', $this->email) === 0;
    }

    /**
     * Check if user is a regular user
     * Regular user: email starts with a digit (e.g., 29202920@seiei.ac.jp)
     */
    public function isRegularUser(): bool
    {
        // Regular users have emails starting with a digit
        return preg_match('/^\d/', $this->email) === 1;
    }

    /**
     * Get the team identifier from email
     * For users whose email starts with a digit, extract the team number
     */
    public function getTeamId(): ?string
    {
        // Only regular users (email starts with digit) have teams
        if (!$this->isRegularUser()) {
            return null;
        }
        
        // Extract digits from the beginning of email
        // Example: 29202920@seiei.ac.jp -> 29202920
        preg_match('/^(\d+)/', $this->email, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Check if user belongs to the same team as another user
     */
    public function isSameTeam(User $otherUser): bool
    {
        $myTeam = $this->getTeamId();
        $otherTeam = $otherUser->getTeamId();
        
        if ($myTeam === null || $otherTeam === null) {
            return false;
        }
        
        return $myTeam === $otherTeam;
    }

    /**
     * Get the plans for the user.
     */
    public function plans()
    {
        return $this->hasMany(Plan::class);
    }
}
