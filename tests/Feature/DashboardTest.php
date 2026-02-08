<?php

use App\Livewire\Dashboard;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

// ==========================================
// ACCESS TESTS
// ==========================================

test('guest cannot access dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated user can access dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Dashboard');
});

// ==========================================
// STATS TESTS
// ==========================================

test('dashboard shows total products count', function () {
    $user = User::factory()->create();
    Product::factory(5)->create();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee('5');
});

test('dashboard shows active and inactive product counts', function () {
    $user = User::factory()->create();
    Product::factory(3)->create(['is_active' => true]);
    Product::factory(2)->create(['is_active' => false]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);
    $component->assertSeeInOrder(['3', '2']);
});

test('dashboard shows zero counts when no data', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee('0');
});

test('admin dashboard shows categories count', function () {
    $admin = User::factory()->admin()->create();
    Category::factory(4)->create();

    Livewire::actingAs($admin)
        ->test(Dashboard::class)
        ->assertSee('4');
});

test('admin dashboard shows total users count', function () {
    $admin = User::factory()->admin()->create();
    User::factory(2)->create();

    Livewire::actingAs($admin)
        ->test(Dashboard::class)
        ->assertSee('3'); // admin + 2 users
});

// ==========================================
// RECENT ACTIVITY TESTS
// ==========================================

test('dashboard shows recent products', function () {
    $user = User::factory()->create();
    Product::factory()->create(['name' => 'Recent Product']);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee('Recent Product');
});

test('dashboard shows max 5 recent products', function () {
    $user = User::factory()->create();
    Product::factory(7)->create();

    $component = Livewire::actingAs($user)->test(Dashboard::class);
    $recentProducts = $component->viewData('recentProducts');

    expect($recentProducts)->toHaveCount(5);
});

test('dashboard shows product category in recent activity', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Electronics']);
    Product::factory()->create(['name' => 'Laptop', 'category_id' => $category->id]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee('Laptop')
        ->assertSee('Electronics');
});

test('dashboard shows empty state when no products', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee('No hay productos');
});

// ==========================================
// QUICK ACTIONS TESTS
// ==========================================

test('dashboard shows quick action links', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee('Gestionar Productos');
});

test('admin sees admin quick actions', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Dashboard::class)
        ->assertSee('Gestionar Categorías')
        ->assertSee('Exportar Productos CSV');
});

test('regular user does not see admin quick actions', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertDontSee('Gestionar Categorías')
        ->assertDontSee('Exportar Productos CSV');
});
