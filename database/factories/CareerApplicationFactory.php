<?php

namespace Database\Factories;

use App\Models\Career;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CareerApplication>
 */
class CareerApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $career = Career::factory()->create();

        return [
            'career_id' => $career->id,
            'full_name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'location' => $this->faker->city().', '.$this->faker->country(),
            'role_applied_for' => $career->role_title.' — '.$career->venture_name,
            'relevant_experience' => $this->faker->paragraphs(3, true),
            'profile_link' => $this->faker->url(),
            'status' => $this->faker->randomElement(['pending', 'reviewing', 'shortlisted', 'rejected', 'closed']),
            'admin_notes' => $this->faker->optional()->sentence(),
            'reviewed_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'reviewed_by' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'pending', 'reviewed_at' => null]);
    }

    public function shortlisted(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'shortlisted']);
    }
}
