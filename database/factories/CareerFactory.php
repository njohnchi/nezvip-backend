<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Career>
 */
class CareerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'venture_name' => $this->faker->randomElement(['Gasmeat Bistro', 'Tan-Kuma Instant Stew', 'Agririthm', 'Amjirbur', 'NezVIP Platform']),
            'role_title' => $this->faker->jobTitle(),
            'location' => $this->faker->randomElement(['Lagos, Nigeria', 'Abuja, Nigeria', 'Remote', 'Hybrid — Lagos']),
            'engagement_type' => $this->faker->randomElement(['Full-time', 'Part-time', 'Contract', 'Retainer']),
            'venture_context' => $this->faker->paragraphs(2, true),
            'responsibilities' => $this->faker->paragraphs(3, true),
            'execution_expectations' => $this->faker->paragraphs(2, true),
            'is_active' => $this->faker->boolean(80),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
