<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Animal extends Model
{
    use HasFactory;
    use HasTranslations;

    /**
     * @var array<int, string>
     */
    public array $translatable = [
        'name',
        'description',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the animal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
