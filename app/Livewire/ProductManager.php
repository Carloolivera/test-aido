<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ProductManager extends Component
{
    use WithPagination;

    public $productId;
    public $name;
    public $description;
    public $price;
    public $is_active = true;
    public $category_id; // Added category_id

    public $categories; // To hold the list of categories

    public $isEditing = false;
    public $showModal = false;
    public $search = '';

    public $showDeleteModal = false;
    public $deleteProductId = null;
    public $deleteProductName = '';

    // Filters
    public $filterCategory = '';
    public $filterStatus = '';

    public function updatedFilterCategory()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterCategory', 'filterStatus']);
        $this->resetPage();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:products,name,' . ($this->productId ?? 'NULL'),
            'description' => 'nullable|string|max:1000',
            'price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
        ];
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['name', 'description', 'price', 'is_active', 'category_id', 'isEditing', 'productId']);
        $this->is_active = true;
        $this->showModal = true;
        // Load categories when opening modal
        $this->categories = \App\Models\Category::where('is_active', true)->get();
    }

    public function edit($id)
    {
        $this->isEditing = true;
        $product = Product::findOrFail($id);
        $this->productId = $product->id;

        $this->name = $product->name;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->price = $product->price;
        $this->is_active = $product->is_active;
        $this->category_id = $product->category_id;

        $this->categories = \App\Models\Category::where('is_active', true)->get();
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $product = Product::findOrFail($this->productId);
            $product->update([
                'name' => $this->name,
                'description' => $this->description,
                'price' => $this->price,
                'is_active' => $this->is_active,
                'category_id' => $this->category_id,
            ]);
            session()->flash('message', 'Product actualizado exitosamente.');
        } else {
            Product::create([
                'name' => $this->name,
                'description' => $this->description,
                'price' => $this->price,
                'is_active' => $this->is_active,
                'category_id' => $this->category_id,
            ]);
            session()->flash('message', 'Product creado exitosamente.');
        }

        $this->showModal = false;
        $this->showModal = false;
        $this->reset(['name', 'description', 'price', 'is_active', 'category_id', 'isEditing', 'productId']);
    }

    public function confirmDelete($id)
    {
        $product = Product::findOrFail($id);
        $this->deleteProductId = $id;
        $this->deleteProductName = $product->name;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deleteProductId = null;
        $this->deleteProductName = '';
    }

    public function delete()
    {
        Product::findOrFail($this->deleteProductId)->delete();
        $this->showDeleteModal = false;
        $this->deleteProductId = null;
        $this->deleteProductName = '';
        session()->flash('message', 'Product eliminado exitosamente.');
    }

    public function render()
    {
        return view('livewire.product-manager', [
            'products' => Product::query()
                ->with('category')
                ->when($this->search, fn($query) =>
                    $query->where('name', 'like', '%' . $this->search . '%')
                )
                ->when($this->filterCategory !== '', fn($query) =>
                    $query->where('category_id', $this->filterCategory ?: null)
                )
                ->when($this->filterStatus !== '', fn($query) =>
                    $query->where('is_active', $this->filterStatus)
                )
                ->latest()
                ->paginate(10),
            'allCategories' => \App\Models\Category::where('is_active', true)->get(),
        ]);
    }
}
