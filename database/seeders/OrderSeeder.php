<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Order::create([
            'user_id' => 4,
            'product_id' => 1,
            'order_number' => '2321690780707',
            'quantity' => 1,
            'shipping_cost' => 100,
            'payment' => 'COD',
            'price' => '40000',
            'printed' => 0
        ]);
    }
}
