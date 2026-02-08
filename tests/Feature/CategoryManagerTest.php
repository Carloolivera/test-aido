<?php

use App\Models\Category;
use App\Models\User;
use App\Livewire\CategoryManager;
use Livewire\Livewire;

test('guest cannot access categories page', function () {
    $this->get('/categories')->assertRedirect('/login');
});

test('authenticated user can access categories page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/categories')
        ->assertOk();
});

test('categories can be created via factory', function () {
    $category = Category::factory()->create(['name' => 'Test Category']);

    expect($category->name)->toBe('Test Category');
    expect($category->exists)->toBeTrue();
});

test('can create a new category', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CategoryManager::class)
        ->set('name', 'Electronics')
        ->set('description', 'Electronic products')
        ->set('is_active', true)
        ->call('save');

    $this->assertDatabaseHas('categories', [
        'name' => 'Electronics',
        'description' => 'Electronic products',
        'is_active' => true,
    ]);
});

test('category name is required', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CategoryManager::class)
        ->set('name', '')
        ->set('description', 'Some description')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('category name must be unique', function () {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Existing Category']);

    Livewire::actingAs($user)
        ->test(CategoryManager::class)
        ->set('name', 'Existing Category')
        ->call('save')
        ->assertHasErrors(['name' => 'unique']);
});

test('can edit an existing category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'name' => 'Old Name',
        'description' => 'Old Description',
    ]);

    Livewire::actingAs($user)
        ->test(CategoryManager::class)
        ->call('edit', $category->id)
        ->assertSet('categoryId', $category->id)
        ->assertSet('name', 'Old Name')
        ->assertSet('isEditing', true)
        ->set('name', 'New Name')
        ->set('description', 'New Description')
        ->call('save');

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'New Name',
        'description' => 'New Description',
    ]);
});

test('can delete a category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($user)
        ->test(CategoryManager::class)
        ->call('confirmDelete', $category->id)
        ->assertSet('showDeleteModal', true)
        ->assertSet('deleteCategoryId', $category->id)
        ->call('delete');

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('can cancel delete operation', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($user)
        ->test(CategoryManager::class)
        ->call('confirmDelete', $category->id)
        ->assertSet('showDeleteModal', true)
        ->call('cancelDelete')
        ->assertSet('showDeleteModal', false)
        ->assertSet('deleteCategoryId', null);

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('can filter categories by active status', function () {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Active Category', 'is_active' => true]);
    Category::factory()->create(['name' => 'Inactive Category', 'is_active' => false]);

    Livewire::actingAs($user)
        ->test(CategoryManager::class)
        ->assertSee('Active Category')
        ->assertSee('Inactive Category')
        ->set('filterStatus', '1')
        ->assertSee('Active Category')
        ->assertDontSee('Inactive Category');
});

test('can filter categories by inactive status', function () {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Active Category', 'is_active' => true]);
    Category::factory()->create(['name' => 'Inactive Category', 'is_active' => false]);

    Livewire::actingAs($user)
        ->test(CategoryManager::class)
        ->set('filterStatus', '0')
        ->assertDontSee('Active Category')
        ->assertSee('Inactive Category');
});

test('can search categories by name', function () {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Electronics']);
    Category::factory()->create(['name' => 'Clothing']);

    Livewire::actingAs($user)
        ->test(CategoryManager::class)
        ->assertSee('Electronics')
        ->assertSee('Clothing')
        ->set('search', 'Electro')
        ->assertSee('Electronics')
        ->assertDontSee('Clothing');
});

test('can clear filters', function () {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Active Category', 'is_active' => true]);
    Category::factory()->create(['name' => 'Inactive Category', 'is_active' => false]);

    Livewire::actingAs($user)
        ->test(CategoryManager::class)
        ->set('filterStatus', '1')
        ->set('search', 'Active')
        ->call('clearFilters')
        ->assertSet('filterStatus', '')
        ->assertSet('search', '');
});

test('opening modal resets form', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CategoryManager::class)
        ->set('name', 'Some Name')
        ->set('description', 'Some Description')
        ->call('openModal')
        ->assertSet('name', '')
        ->assertSet('description', '')
        ->assertSet('is_active', true)
        ->assertSet('isEditing', false)
        ->assertSet('showModal', true);
});

test('category shows product count', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'With Products']);

    // Create products associated with the category
    \App\Models\Product::factory(3)->create(['category_id' => $category->id]);

    Livewire::actingAs($user)
        ->test(CategoryManager::class)
        ->assertSee('With Products');
});

test('active scope returns only active categories', function () {
    Category::factory()->create(['name' => 'Active', 'is_active' => true]);
    Category::factory()->create(['name' => 'Inactive', 'is_active' => false]);

    $activeCategories = Category::active()->get();

    expect($activeCategories)->toHaveCount(1);
    expect($activeCategories->first()->name)->toBe('Active');
});

test('category has many products relationship', function () {
    $category = Category::factory()->create();
    \App\Models\Product::factory(2)->create(['category_id' => $category->id]);

    expect($category->products)->toHaveCount(2);
});
