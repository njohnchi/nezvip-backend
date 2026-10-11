<?php

namespace Database\Factories;

use App\Models\FormDraft;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormDraft>
 */
class FormDraftFactory extends Factory
{
    protected $model = FormDraft::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'draft_reference' => 'DRF-'.strtoupper(Str::random(10)),
            'form_type' => 'venture_synthesis',
            'email' => $this->faker->safeEmail(),
            'case_reference' => null,
            'data' => [],
            'current_step' => null,
            'status' => 'draft',
            'last_saved_at' => now(),
        ];
    }
}
