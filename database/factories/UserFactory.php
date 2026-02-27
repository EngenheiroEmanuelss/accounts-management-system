<?php

namespace Database\Factories;

use Faker\Provider\pt_BR\Person as PersonFaker;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $this->faker->addProvider(new PersonFaker($this->faker));

        return [
            'name'     => $this->faker->name(),
            'email'    => $this->faker->unique()->safeEmail(),
            'password' => Str::password(length: 8),
            'document' => $this->faker->cpf(false),
        ];
    }
}
