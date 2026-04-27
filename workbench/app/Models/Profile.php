<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Profile extends Model
{
    use HasFactory;
    use HasTranslations;

    /**
     * @var array<int, string>
     */
    public array $translatable = [
        'headline',
        'biography',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'headline',
        'biography',
        'is_public',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'headline' => 'array',
            'biography' => 'array',
            'is_public' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
