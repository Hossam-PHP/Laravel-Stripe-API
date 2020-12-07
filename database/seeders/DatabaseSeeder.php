<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory(10)->create();
        for($i = 1; $i <= 9; $i++) {
        $product = new \App\Models\Product([
            'imagePath' => 'https://www.sorbet.co.za/wp-content/uploads/2016/02/bathandbody_300x300-1.jpg',
            'title' => 'Product nr - ' .$i. ' - Hand Cream '. rand(90, 150) .'ml',
            'description' => 'Super dry hand cream with small glass pieces for good fresh feelings.',
            'price' => rand(20, 200)
        ]);

        $product->save();
        }
    }
}
