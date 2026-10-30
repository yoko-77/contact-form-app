<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'first_name' => fake()->lastName(),
            'last_name' => fake()->firstName(),
            'gender' => fake()->randomElement([1, 2, 3]),
            'email' => fake()->unique()->safeEmail(),
            'tel' => str_replace('-', '', fake()->phoneNumber()),
            'address' => fake()->prefecture().fake()->city().fake()->streetAddress(),
            'building' => fake()->secondaryAddress(),
            'detail' => fake()->realText(120),
        ];
    }
}
