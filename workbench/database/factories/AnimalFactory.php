<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Infinity\FilamentTranslatable\Enums\Locale;
use Workbench\App\Models\Animal;

/**
 * @extends Factory<Animal>
 */
class AnimalFactory extends Factory
{
    protected $model = Animal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => [
                Locale::English->value => 'Wolf',
                Locale::Bulgarian->value => 'Вълк',
            ],
            'description' => [
                Locale::English->value => 'A social forest animal.',
                Locale::Bulgarian->value => 'Социално горско животно.',
            ],
            'is_active' => true,
        ];
    }
}
