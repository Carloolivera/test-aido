<?php

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

// Authentication Tests
test('unauthenticated user cannot access products api', function () {
    $this->getJson('/api/products')
        ->assertUnauthorized();
});

test('authenticated user can access products api', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/products')
        ->assertOk();
});

// Index Tests
test('can list all products', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Product::factory(5)->create();

    $this->getJson('/api/products')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'description', 'price', 'is_active']
            ]
        ]);
});

test('products are paginated', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Product::factory(20)->create();

    $response = $this->getJson('/api/products')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['current_page', 'per_page', 'total']
        ]);

    expect($response->json('meta.per_page'))->toBe(15);
});

test('can search products by name', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Product::factory()->create(['name' => 'iPhone 15 Pro']);
    Product::factory()->create(['name' => 'Samsung Galaxy']);

    $this->getJson('/api/products?search=iPhone')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['name' => 'iPhone 15 Pro']);
});

// Store Tests (admin only)
test('can create a product', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $productData = [
        'name' => 'New Product',
        'description' => 'Product description',
        'price' => 99.99,
        'is_active' => true,
    ];

    $this->postJson('/api/products', $productData)
        ->assertCreated()
        ->assertJsonFragment(['name' => 'New Product'])
        ->assertJsonFragment(['message' => 'Product created successfully']);

    $this->assertDatabaseHas('products', ['name' => 'New Product']);
});

test('can create a product with category', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $category = Category::factory()->create();

    $productData = [
        'name' => 'Categorized Product',
        'price' => 50.00,
        'category_id' => $category->id,
    ];

    $this->postJson('/api/products', $productData)
        ->assertCreated();

    $this->assertDatabaseHas('products', [
        'name' => 'Categorized Product',
        'category_id' => $category->id,
    ]);
});

test('product name is required', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $this->postJson('/api/products', [
        'price' => 99.99,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('product price is required', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $this->postJson('/api/products', [
        'name' => 'Test Product',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['price']);
});

test('product price must be positive', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $this->postJson('/api/products', [
        'name' => 'Test Product',
        'price' => -10,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['price']);
});

test('product name must be unique', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    Product::factory()->create(['name' => 'Existing Product']);

    $this->postJson('/api/products', [
        'name' => 'Existing Product',
        'price' => 99.99,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('category_id must exist in categories table', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $this->postJson('/api/products', [
        'name' => 'Test Product',
        'price' => 99.99,
        'category_id' => 9999,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);
});

// Show Tests
test('can view a single product', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $product = Product::factory()->create(['name' => 'Single Product']);

    $this->getJson("/api/products/{$product->id}")
        ->assertOk()
        ->assertJsonFragment(['name' => 'Single Product']);
});

test('returns 404 for non-existent product', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/products/9999')
        ->assertNotFound();
});

// Update Tests (admin only)
test('can update a product', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $product = Product::factory()->create(['name' => 'Old Name']);

    $this->putJson("/api/products/{$product->id}", [
        'name' => 'Updated Name',
        'price' => 199.99,
    ])
        ->assertOk()
        ->assertJsonFragment(['name' => 'Updated Name'])
        ->assertJsonFragment(['message' => 'Product updated successfully']);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Name',
        'price' => 199.99,
    ]);
});

test('can partially update a product', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $product = Product::factory()->create([
        'name' => 'Original Name',
        'price' => 100.00,
    ]);

    $this->putJson("/api/products/{$product->id}", [
        'price' => 150.00,
    ])
        ->assertOk();

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Original Name',
        'price' => 150.00,
    ]);
});

test('cannot update product with duplicate name', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    Product::factory()->create(['name' => 'Existing Product']);
    $product = Product::factory()->create(['name' => 'My Product']);

    $this->putJson("/api/products/{$product->id}", [
        'name' => 'Existing Product',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('can update product with same name', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $product = Product::factory()->create(['name' => 'Same Name']);

    $this->putJson("/api/products/{$product->id}", [
        'name' => 'Same Name',
        'price' => 200.00,
    ])
        ->assertOk();
});

// Delete Tests (admin only)
test('can delete a product', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $product = Product::factory()->create();

    $this->deleteJson("/api/products/{$product->id}")
        ->assertOk()
        ->assertJsonFragment(['message' => 'Product deleted successfully']);

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

// Unauthenticated Access Tests
test('unauthenticated user cannot create product', function () {
    $this->postJson('/api/products', [
        'name' => 'Test',
        'price' => 100,
    ])
        ->assertUnauthorized();
});

test('unauthenticated user cannot update product', function () {
    $product = Product::factory()->create();

    $this->putJson("/api/products/{$product->id}", [
        'name' => 'Updated',
    ])
        ->assertUnauthorized();
});

test('unauthenticated user cannot delete product', function () {
    $product = Product::factory()->create();

    $this->deleteJson("/api/products/{$product->id}")
        ->assertUnauthorized();
});

// Products ordered by latest
test('products are ordered by latest first', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $oldProduct = Product::factory()->create(['name' => 'Old Product']);
    sleep(1);
    $newProduct = Product::factory()->create(['name' => 'New Product']);

    $response = $this->getJson('/api/products')->assertOk();

    $products = $response->json('data');
    expect($products[0]['name'])->toBe('New Product');
    expect($products[1]['name'])->toBe('Old Product');
});
