<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Infinity\FilamentTranslatable\Enums\Locale;
use Workbench\App\Models\Profile;
use Workbench\App\Models\User;

/**
 * @template TModel of \Workbench\App\Models\Profile
 *
 * @extends Factory<TModel>
 */
class ProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = Profile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'headline' => [
                Locale::English->value => fake()->jobTitle(),
                Locale::Bulgarian->value => fake()->sentence(3),
            ],
            'biography' => [
                Locale::English->value => fake()->paragraph(),
                Locale::Bulgarian->value => fake()->paragraph(),
            ],
            'is_public' => true,
        ];
    }
}
