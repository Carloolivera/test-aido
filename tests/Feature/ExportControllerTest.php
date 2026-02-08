<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

// ==========================================
// ACCESS TESTS
// ==========================================

test('guest cannot access products csv export', function () {
    $this->get('/export/products/csv')->assertRedirect('/login');
});

test('guest cannot access products excel export', function () {
    $this->get('/export/products/excel')->assertRedirect('/login');
});

test('guest cannot access categories csv export', function () {
    $this->get('/export/categories/csv')->assertRedirect('/login');
});

test('guest cannot access categories excel export', function () {
    $this->get('/export/categories/excel')->assertRedirect('/login');
});

// ==========================================
// PRODUCTS CSV EXPORT TESTS
// ==========================================

test('authenticated user can export products as csv', function () {
    $user = User::factory()->create();
    Product::factory()->create(['name' => 'Test Product']);

    $response = $this->actingAs($user)
        ->get('/export/products/csv');

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

test('products csv contains correct headers', function () {
    $user = User::factory()->create();
    Product::factory()->create();

    $response = $this->actingAs($user)
        ->get('/export/products/csv');

    $content = $response->streamedContent();

    expect($content)->toContain('ID');
    expect($content)->toContain('Nombre');
    expect($content)->toContain('Categoría');
    expect($content)->toContain('Descripción');
    expect($content)->toContain('Precio');
    expect($content)->toContain('Estado');
    expect($content)->toContain('Creado');
});

test('products csv contains product data', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Electronics']);
    Product::factory()->create([
        'name' => 'Laptop HP',
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->get('/export/products/csv');

    $content = $response->streamedContent();

    expect($content)->toContain('Laptop HP');
    expect($content)->toContain('Electronics');
    expect($content)->toContain('Activo');
});

test('products csv shows sin categoria for null category', function () {
    $user = User::factory()->create();
    Product::factory()->create([
        'name' => 'Uncategorized Product',
        'category_id' => null,
    ]);

    $response = $this->actingAs($user)
        ->get('/export/products/csv');

    $content = $response->streamedContent();

    expect($content)->toContain('Sin categoría');
});

test('products csv filters by search', function () {
    $user = User::factory()->create();
    Product::factory()->create(['name' => 'Apple iPhone']);
    Product::factory()->create(['name' => 'Samsung Galaxy']);

    $response = $this->actingAs($user)
        ->get('/export/products/csv?search=Apple');

    $content = $response->streamedContent();

    expect($content)->toContain('Apple iPhone');
    expect($content)->not->toContain('Samsung Galaxy');
});

test('products csv filters by category', function () {
    $user = User::factory()->create();
    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();
    Product::factory()->create(['name' => 'Cat1 Product', 'category_id' => $cat1->id]);
    Product::factory()->create(['name' => 'Cat2 Product', 'category_id' => $cat2->id]);

    $response = $this->actingAs($user)
        ->get('/export/products/csv?category_id=' . $cat1->id);

    $content = $response->streamedContent();

    expect($content)->toContain('Cat1 Product');
    expect($content)->not->toContain('Cat2 Product');
});

test('products csv filters by status', function () {
    $user = User::factory()->create();
    Product::factory()->create(['name' => 'Active Product', 'is_active' => true]);
    Product::factory()->create(['name' => 'Inactive Product', 'is_active' => false]);

    $response = $this->actingAs($user)
        ->get('/export/products/csv?status=1');

    $content = $response->streamedContent();

    expect($content)->toContain('Active Product');
    expect($content)->not->toContain('Inactive Product');
});

// ==========================================
// PRODUCTS EXCEL EXPORT TESTS
// ==========================================

test('authenticated user can export products as excel', function () {
    $user = User::factory()->create();
    Product::factory()->create();

    $response = $this->actingAs($user)
        ->get('/export/products/excel');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.ms-excel');
});

test('products excel contains html table structure', function () {
    $user = User::factory()->create();
    Product::factory()->create(['name' => 'Test Product']);

    $response = $this->actingAs($user)
        ->get('/export/products/excel');

    $content = $response->streamedContent();

    expect($content)->toContain('<html');
    expect($content)->toContain('<table');
    expect($content)->toContain('Test Product');
    expect($content)->toContain('</table>');
    expect($content)->toContain('</html>');
});

test('products excel filters by search', function () {
    $user = User::factory()->create();
    Product::factory()->create(['name' => 'Apple iPhone']);
    Product::factory()->create(['name' => 'Samsung Galaxy']);

    $response = $this->actingAs($user)
        ->get('/export/products/excel?search=Apple');

    $content = $response->streamedContent();

    expect($content)->toContain('Apple iPhone');
    expect($content)->not->toContain('Samsung Galaxy');
});

// ==========================================
// CATEGORIES CSV EXPORT TESTS
// ==========================================

test('authenticated user can export categories as csv', function () {
    $user = User::factory()->create();
    Category::factory()->create();

    $response = $this->actingAs($user)
        ->get('/export/categories/csv');

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

test('categories csv contains correct headers', function () {
    $user = User::factory()->create();
    Category::factory()->create();

    $response = $this->actingAs($user)
        ->get('/export/categories/csv');

    $content = $response->streamedContent();

    expect($content)->toContain('ID');
    expect($content)->toContain('Nombre');
    expect($content)->toContain('Descripción');
    expect($content)->toContain('Productos');
    expect($content)->toContain('Estado');
    expect($content)->toContain('Creado');
});

test('categories csv contains category data', function () {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Electronics', 'is_active' => true]);

    $response = $this->actingAs($user)
        ->get('/export/categories/csv');

    $content = $response->streamedContent();

    expect($content)->toContain('Electronics');
    expect($content)->toContain('Activo');
});

test('categories csv shows product count', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'With Products']);
    Product::factory(3)->create(['category_id' => $category->id]);

    $response = $this->actingAs($user)
        ->get('/export/categories/csv');

    $content = $response->streamedContent();

    expect($content)->toContain('With Products');
    expect($content)->toContain(',3,'); // product count in CSV
});

test('categories csv filters by search', function () {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Electronics']);
    Category::factory()->create(['name' => 'Clothing']);

    $response = $this->actingAs($user)
        ->get('/export/categories/csv?search=Electro');

    $content = $response->streamedContent();

    expect($content)->toContain('Electronics');
    expect($content)->not->toContain('Clothing');
});

test('categories csv filters by status', function () {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Active Cat', 'is_active' => true]);
    Category::factory()->create(['name' => 'Inactive Cat', 'is_active' => false]);

    $response = $this->actingAs($user)
        ->get('/export/categories/csv?status=0');

    $content = $response->streamedContent();

    expect($content)->toContain('Inactive Cat');
    expect($content)->not->toContain('Active Cat');
});

// ==========================================
// CATEGORIES EXCEL EXPORT TESTS
// ==========================================

test('authenticated user can export categories as excel', function () {
    $user = User::factory()->create();
    Category::factory()->create();

    $response = $this->actingAs($user)
        ->get('/export/categories/excel');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.ms-excel');
});

test('categories excel contains html table structure', function () {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Test Category']);

    $response = $this->actingAs($user)
        ->get('/export/categories/excel');

    $content = $response->streamedContent();

    expect($content)->toContain('<html');
    expect($content)->toContain('<table');
    expect($content)->toContain('Test Category');
    expect($content)->toContain('</table>');
});

test('categories excel filters by search', function () {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Electronics']);
    Category::factory()->create(['name' => 'Clothing']);

    $response = $this->actingAs($user)
        ->get('/export/categories/excel?search=Electro');

    $content = $response->streamedContent();

    expect($content)->toContain('Electronics');
    expect($content)->not->toContain('Clothing');
});

// ==========================================
// EMPTY DATA TESTS
// ==========================================

test('products csv works with no products', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get('/export/products/csv');

    $response->assertOk();

    $content = $response->streamedContent();
    expect($content)->toContain('ID'); // Headers still present
});

test('categories csv works with no categories', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get('/export/categories/csv');

    $response->assertOk();

    $content = $response->streamedContent();
    expect($content)->toContain('ID'); // Headers still present
});
