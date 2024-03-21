<@php
declare(strict_types=1);

namespace {namespace};

use Faker\Generator;
use {model};

class {class} extends {modelName}
{
    public function fake(Generator &$faker): array
    {
        // Test example
        return [
            'name'       => $faker->firstName,
            'email'      => $faker->email,
            'phone'      => $faker->phoneNumber,
            'title'      => $faker->word(),
            'code'       => $faker->numberBetween(1000, 9999),
            'active'     => $faker->numberBetween(0, 1),
            'expires_at' => $faker->dateTimeBetween('-1 year', '1 year')
        ];
    }
}
