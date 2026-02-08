<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

// ==========================================
// USER MODEL TESTS
// ==========================================

test('user isAdmin returns true for admin role', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->isAdmin())->toBeTrue();
});

test('user isAdmin returns false for user role', function () {
    $user = User::factory()->create();

    expect($user->isAdmin())->toBeFalse();
});

test('user factory defaults to user role', function () {
    $user = User::factory()->create();

    expect($user->role)->toBe('user');
});

test('user factory admin state sets admin role', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->role)->toBe('admin');
});

// ==========================================
// WEB ROUTE PROTECTION TESTS
// ==========================================

test('regular user cannot access categories page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/categories')
        ->assertForbidden();
});

test('admin user can access categories page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/categories')
        ->assertOk();
});

test('regular user cannot access export routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/export/products/csv')->assertForbidden();
    $this->actingAs($user)->get('/export/products/excel')->assertForbidden();
    $this->actingAs($user)->get('/export/categories/csv')->assertForbidden();
    $this->actingAs($user)->get('/export/categories/excel')->assertForbidden();
});

test('admin user can access export routes', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->create();

    $this->actingAs($admin)
        ->get('/export/products/csv')
        ->assertOk();
});

test('regular user can still access products page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/products')
        ->assertOk();
});

test('regular user can still access dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});

// ==========================================
// API ROUTE PROTECTION TESTS
// ==========================================

test('regular api user can list products', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/products')->assertOk();
});

test('regular api user can view single product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/products/' . $product->id)->assertOk();
});

test('regular api user cannot create product', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/products', [
        'name' => 'Test Product',
        'price' => 99.99,
    ])->assertForbidden();
});

test('admin api user can create product', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $this->postJson('/api/products', [
        'name' => 'Test Product',
        'price' => 99.99,
    ])->assertCreated();
});

test('regular api user cannot update product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    Sanctum::actingAs($user);

    $this->putJson('/api/products/' . $product->id, [
        'name' => 'Updated',
        'price' => 50.00,
    ])->assertForbidden();
});

test('regular api user cannot delete product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    Sanctum::actingAs($user);

    $this->deleteJson('/api/products/' . $product->id)->assertForbidden();
});

// ==========================================
// MIDDLEWARE JSON RESPONSE TEST
// ==========================================

test('admin middleware returns json 403 for api requests', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/products', [
        'name' => 'Test',
        'price' => 10,
    ]);

    $response->assertForbidden()
        ->assertJson(['message' => 'Forbidden. Admin access required.']);
});

// ==========================================
// NAVIGATION VISIBILITY TESTS
// ==========================================

test('admin sees categories link in navigation', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertSee('Categories');
});

test('regular user does not see categories link in navigation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertDontSee('Categories');
});

// ==========================================
// PROFILE ROLE BADGE TESTS
// ==========================================

test('admin sees admin badge in profile', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/profile')
        ->assertSee('Admin');
});

test('regular user sees user badge in profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/profile')
        ->assertSee('User');
});
