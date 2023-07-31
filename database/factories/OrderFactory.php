<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            // 從user model中隨機取出 user_id
            'user_id' => User::inRandomOrder()->value('id'),
            'product_id' => 1,
            'order_number' => date('Ymd') . Str::random(8),
            'quantity' => 1,
            'shipping_cost' => 100,
            'payment' => 'COD',
            'price' => '40000',
            'printed' => 0
        ];
    }
}
