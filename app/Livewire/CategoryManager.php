<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CategoryManager extends Component
{
    use WithPagination;

    public $categoryId;
    public $name;
    public $description;
    public $is_active = true;

    public $isEditing = false;
    public $showModal = false;
    public $search = '';

    public $showDeleteModal = false;
    public $deleteCategoryId = null;
    public $deleteCategoryName = '';

    // Filters
    public $filterStatus = '';

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterStatus']);
        $this->resetPage();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name,' . ($this->categoryId ?? 'NULL'),
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ];
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['name', 'description', 'is_active', 'isEditing', 'categoryId']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->isEditing = true;
        $category = Category::findOrFail($id);
        $this->categoryId = $category->id;

        $this->name = $category->name;
        $this->description = $category->description;
        $this->is_active = $category->is_active;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $category = Category::findOrFail($this->categoryId);
            $category->update([
                'name' => $this->name,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Categoría actualizada exitosamente.');
        } else {
            Category::create([
                'name' => $this->name,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Categoría creada exitosamente.');
        }

        $this->showModal = false;
        $this->reset(['name', 'description', 'is_active', 'isEditing', 'categoryId']);
    }

    public function confirmDelete($id)
    {
        $category = Category::findOrFail($id);
        $this->deleteCategoryId = $id;
        $this->deleteCategoryName = $category->name;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deleteCategoryId = null;
        $this->deleteCategoryName = '';
    }

    public function delete()
    {
        Category::findOrFail($this->deleteCategoryId)->delete();
        $this->showDeleteModal = false;
        $this->deleteCategoryId = null;
        $this->deleteCategoryName = '';
        session()->flash('message', 'Categoría eliminada exitosamente.');
    }

    public function render()
    {
        return view('livewire.category-manager', [
            'categories' => Category::query()
                ->when($this->search, fn($query) =>
                    $query->where('name', 'like', '%' . $this->search . '%')
                )
                ->when($this->filterStatus !== '', fn($query) =>
                    $query->where('is_active', $this->filterStatus)
                )
                ->withCount('products')
                ->latest()
                ->paginate(10)
        ]);
    }
}
