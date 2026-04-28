<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Module>
 */
class ModuleFactory extends Factory
{

    public function definition(): array
    {
        return [
            'name'=>$this->faker->sentence(),
            //
        ];
    }
}
