<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductManager extends Component
{
    use WithPagination;

    public $productId;
    public $name;
    public $description;
    public $price;
    public $is_active = true;

    public $isEditing = false;
    public $showModal = false;
    public $search = '';

    protected $listeners = ['delete'];

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:products,name,' . ($this->productId ?? 'NULL'),
            'description' => 'nullable|string|max:1000',
            'price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['name', 'description', 'price', 'is_active', 'isEditing', 'productId']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->isEditing = true;
        $product = Product::findOrFail($id);
        $this->productId = $product->id;

        $this->name = $product->name;
        $this->description = $product->description;
        $this->price = $product->price;
        $this->is_active = $product->is_active;

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
            ]);
            session()->flash('message', 'Product actualizado exitosamente.');
        } else {
            Product::create([
                'name' => $this->name,
                'description' => $this->description,
                'price' => $this->price,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Product creado exitosamente.');
        }

        $this->showModal = false;
        $this->reset(['name', 'description', 'price', 'is_active', 'isEditing', 'productId']);
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function delete($id)
    {
        Product::findOrFail($id)->delete();
        session()->flash('message', 'Product eliminado exitosamente.');
    }

    public function render()
    {
        return view('livewire.product-manager', [
            'products' => Product::query()
                ->when($this->search, fn($query) =>
                    $query->where('name', 'like', '%' . $this->search . '%')
                )
                ->latest()
                ->paginate(10)
        ]);
    }
}
