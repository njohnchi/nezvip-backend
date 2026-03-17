<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailTemplate>
 */
class EmailTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'template_'.$this->faker->unique()->slug(2, '_'),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(8),
            'subject' => 'We received your request ({{reference}})',
            'body' => "Hello {{name}},\n\nThank you for your submission to {{app_name}}.\n\nReference: {{reference}}",
            'is_active' => true,
            'available_variables' => ['name', 'reference', 'app_name'],
        ];
    }
}
