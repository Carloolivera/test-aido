<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $isAdmin = auth()->user()->isAdmin();

        $data = [
            'totalProducts' => Product::count(),
            'activeProducts' => Product::where('is_active', true)->count(),
            'inactiveProducts' => Product::where('is_active', false)->count(),
            'recentProducts' => Product::with('category')->latest()->take(5)->get(),
            'isAdmin' => $isAdmin,
        ];

        if ($isAdmin) {
            $data['totalCategories'] = Category::count();
            $data['totalUsers'] = User::count();
        }

        return view('livewire.dashboard', $data);
    }
}
