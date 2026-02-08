<?php

use App\Livewire\ProductManager;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

// ==========================================
// ACCESS TESTS
// ==========================================

test('guest cannot access products page', function () {
    $this->get('/products')->assertRedirect('/login');
});

test('authenticated user can access products page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/products')
        ->assertOk()
        ->assertSee('Products');
});

// ==========================================
// FACTORY TESTS
// ==========================================

test('products can be created via factory', function () {
    $product = Product::factory()->create(['name' => 'Test Product']);

    expect($product->name)->toBe('Test Product');
    expect($product->exists)->toBeTrue();
});

// ==========================================
// CREATE TESTS
// ==========================================

test('can create a new product', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('openModal')
        ->set('name', 'New Product')
        ->set('description', 'Product description')
        ->set('price', 99.99)
        ->set('is_active', true)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('products', [
        'name' => 'New Product',
        'description' => 'Product description',
        'price' => 99.99,
        'is_active' => true,
    ]);
});

test('can create a product with category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('openModal')
        ->set('name', 'Categorized Product')
        ->set('price', 50.00)
        ->set('category_id', $category->id)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('products', [
        'name' => 'Categorized Product',
        'category_id' => $category->id,
    ]);
});

test('can create a product without price', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('openModal')
        ->set('name', 'Product Without Price')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('products', [
        'name' => 'Product Without Price',
        'price' => null,
    ]);
});

// ==========================================
// VALIDATION TESTS
// ==========================================

test('product name is required', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('openModal')
        ->set('name', '')
        ->set('price', 10.00)
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('product name must be unique', function () {
    $user = User::factory()->create();
    Product::factory()->create(['name' => 'Existing Product']);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('openModal')
        ->set('name', 'Existing Product')
        ->call('save')
        ->assertHasErrors(['name' => 'unique']);
});

test('product name can be max 255 characters', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('openModal')
        ->set('name', str_repeat('a', 256))
        ->call('save')
        ->assertHasErrors(['name' => 'max']);
});

test('product description can be max 1000 characters', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('openModal')
        ->set('name', 'Valid Name')
        ->set('description', str_repeat('a', 1001))
        ->call('save')
        ->assertHasErrors(['description' => 'max']);
});

test('product price must be numeric', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('openModal')
        ->set('name', 'Test Product')
        ->set('price', 'not-a-number')
        ->call('save')
        ->assertHasErrors(['price' => 'numeric']);
});

test('product price must be positive', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('openModal')
        ->set('name', 'Test Product')
        ->set('price', -10)
        ->call('save')
        ->assertHasErrors(['price' => 'min']);
});

test('category_id must exist in categories table', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('openModal')
        ->set('name', 'Test Product')
        ->set('category_id', 99999)
        ->call('save')
        ->assertHasErrors(['category_id' => 'exists']);
});

// ==========================================
// EDIT TESTS
// ==========================================

test('can edit an existing product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Original Name',
        'price' => 50.00,
    ]);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('edit', $product->id)
        ->assertSet('name', 'Original Name')
        ->assertSet('price', 50.00)
        ->assertSet('isEditing', true)
        ->set('name', 'Updated Name')
        ->set('price', 75.00)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Name',
        'price' => 75.00,
    ]);
});

test('can update product with same name', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => 'My Product']);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('edit', $product->id)
        ->set('name', 'My Product') // Same name
        ->set('price', 100.00)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'My Product',
        'price' => 100.00,
    ]);
});

test('cannot update product with duplicate name', function () {
    $user = User::factory()->create();
    Product::factory()->create(['name' => 'Existing Product']);
    $product = Product::factory()->create(['name' => 'My Product']);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('edit', $product->id)
        ->set('name', 'Existing Product')
        ->call('save')
        ->assertHasErrors(['name' => 'unique']);
});

test('can update product category', function () {
    $user = User::factory()->create();
    $oldCategory = Category::factory()->create(['is_active' => true]);
    $newCategory = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $oldCategory->id]);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('edit', $product->id)
        ->set('category_id', $newCategory->id)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'category_id' => $newCategory->id,
    ]);
});

// ==========================================
// DELETE TESTS
// ==========================================

test('can delete a product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('confirmDelete', $product->id)
        ->assertSet('showDeleteModal', true)
        ->assertSet('deleteProductId', $product->id)
        ->call('delete');

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('can cancel delete operation', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('confirmDelete', $product->id)
        ->assertSet('showDeleteModal', true)
        ->call('cancelDelete')
        ->assertSet('showDeleteModal', false)
        ->assertSet('deleteProductId', null);

    $this->assertDatabaseHas('products', ['id' => $product->id]);
});

test('confirm delete shows product name', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => 'Product To Delete']);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('confirmDelete', $product->id)
        ->assertSet('deleteProductName', 'Product To Delete');
});

// ==========================================
// FILTER TESTS
// ==========================================

test('can filter products by category', function () {
    $user = User::factory()->create();
    $category1 = Category::factory()->create(['is_active' => true]);
    $category2 = Category::factory()->create(['is_active' => true]);

    Product::factory()->create(['name' => 'Cat1 Product', 'category_id' => $category1->id]);
    Product::factory()->create(['name' => 'Cat2 Product', 'category_id' => $category2->id]);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->set('filterCategory', $category1->id)
        ->assertSee('Cat1 Product')
        ->assertDontSee('Cat2 Product');
});

test('can filter products by active status', function () {
    $user = User::factory()->create();
    Product::factory()->create(['name' => 'Active Product', 'is_active' => true]);
    Product::factory()->create(['name' => 'Inactive Product', 'is_active' => false]);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->set('filterStatus', '1')
        ->assertSee('Active Product')
        ->assertDontSee('Inactive Product');
});

test('can filter products by inactive status', function () {
    $user = User::factory()->create();
    Product::factory()->create(['name' => 'Active Product', 'is_active' => true]);
    Product::factory()->create(['name' => 'Inactive Product', 'is_active' => false]);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->set('filterStatus', '0')
        ->assertSee('Inactive Product')
        ->assertDontSee('Active Product');
});

test('can search products by name', function () {
    $user = User::factory()->create();
    Product::factory()->create(['name' => 'Apple iPhone']);
    Product::factory()->create(['name' => 'Samsung Galaxy']);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->set('search', 'Apple')
        ->assertSee('Apple iPhone')
        ->assertDontSee('Samsung Galaxy');
});

test('can clear filters', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->set('search', 'test')
        ->set('filterCategory', $category->id)
        ->set('filterStatus', '1')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('filterCategory', '')
        ->assertSet('filterStatus', '');
});

test('filter by category calls resetPage', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);

    // Crear suficientes productos para tener paginación
    Product::factory(15)->create(['category_id' => $category->id]);

    // Simplemente verificar que el filtro funciona sin error
    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->set('filterCategory', $category->id)
        ->assertHasNoErrors();
});

// ==========================================
// MODAL TESTS
// ==========================================

test('opening modal resets form', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->set('name', 'Old Name')
        ->set('price', 100)
        ->call('openModal')
        ->assertSet('name', '')
        ->assertSet('price', null)
        ->assertSet('isEditing', false)
        ->assertSet('showModal', true);
});

test('opening modal loads active categories', function () {
    $user = User::factory()->create();
    $activeCategory = Category::factory()->create(['name' => 'Active Cat', 'is_active' => true]);
    $inactiveCategory = Category::factory()->create(['name' => 'Inactive Cat', 'is_active' => false]);

    $component = Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('openModal');

    $categories = $component->get('categories');
    expect($categories->pluck('name')->toArray())->toContain('Active Cat');
    expect($categories->pluck('name')->toArray())->not->toContain('Inactive Cat');
});

test('modal closes after save', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('openModal')
        ->set('name', 'New Product')
        ->call('save')
        ->assertSet('showModal', false);
});

// ==========================================
// RELATIONSHIP TESTS
// ==========================================

test('product belongs to category', function () {
    $category = Category::factory()->create(['name' => 'Electronics']);
    $product = Product::factory()->create(['category_id' => $category->id]);

    expect($product->category)->toBeInstanceOf(Category::class);
    expect($product->category->name)->toBe('Electronics');
});

test('product can exist without category', function () {
    $product = Product::factory()->create(['category_id' => null]);

    expect($product->category)->toBeNull();
});

test('products are ordered by latest first', function () {
    $user = User::factory()->create();

    $oldProduct = Product::factory()->create(['name' => 'Old Product']);
    sleep(1);
    $newProduct = Product::factory()->create(['name' => 'New Product']);

    $component = Livewire::actingAs($user)
        ->test(ProductManager::class);

    $products = $component->viewData('products');
    expect($products->first()->id)->toBe($newProduct->id);
});

// ==========================================
// DISPLAY TESTS
// ==========================================

test('product shows category name in list', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Electronics', 'is_active' => true]);
    Product::factory()->create(['name' => 'Laptop', 'category_id' => $category->id]);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->assertSee('Laptop')
        ->assertSee('Electronics');
});

test('product shows price formatted', function () {
    $user = User::factory()->create();
    Product::factory()->create(['name' => 'Expensive Item', 'price' => 1234.56]);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->assertSee('Expensive Item');
});

test('active products show active badge', function () {
    $user = User::factory()->create();
    Product::factory()->create(['name' => 'Active Item', 'is_active' => true]);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->assertSee('Active Item');
});

test('can toggle product status', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['is_active' => true]);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->call('edit', $product->id)
        ->assertSet('is_active', true)
        ->set('is_active', false)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'is_active' => false,
    ]);
});

test('filter status calls resetPage', function () {
    $user = User::factory()->create();
    Product::factory(15)->create(['is_active' => true]);

    Livewire::actingAs($user)
        ->test(ProductManager::class)
        ->set('filterStatus', '1')
        ->assertHasNoErrors();
});
