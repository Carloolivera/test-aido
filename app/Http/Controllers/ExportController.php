<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function productsCSV(Request $request): StreamedResponse
    {
        $products = Product::with('category')
            ->when($request->search, fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->when($request->category_id !== null && $request->category_id !== '', fn($q) =>
                $q->where('category_id', $request->category_id ?: null)
            )
            ->when($request->status !== null && $request->status !== '', fn($q) =>
                $q->where('is_active', $request->status)
            )
            ->latest()
            ->get();

        $filename = 'products_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Headers
            fputcsv($handle, ['ID', 'Nombre', 'Categoría', 'Descripción', 'Precio', 'Estado', 'Creado']);

            // Data
            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->id,
                    $product->name,
                    $product->category?->name ?? 'Sin categoría',
                    $product->description,
                    $product->price,
                    $product->is_active ? 'Activo' : 'Inactivo',
                    $product->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function productsExcel(Request $request): StreamedResponse
    {
        $products = Product::with('category')
            ->when($request->search, fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->when($request->category_id !== null && $request->category_id !== '', fn($q) =>
                $q->where('category_id', $request->category_id ?: null)
            )
            ->when($request->status !== null && $request->status !== '', fn($q) =>
                $q->where('is_active', $request->status)
            )
            ->latest()
            ->get();

        $filename = 'products_' . now()->format('Y-m-d_His') . '.xls';

        return response()->streamDownload(function () use ($products) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="UTF-8"></head>';
            echo '<body><table border="1">';

            // Headers
            echo '<tr style="background-color:#4472C4;color:white;font-weight:bold;">';
            echo '<th>ID</th><th>Nombre</th><th>Categoría</th><th>Descripción</th><th>Precio</th><th>Estado</th><th>Creado</th>';
            echo '</tr>';

            // Data
            foreach ($products as $product) {
                echo '<tr>';
                echo '<td>' . $product->id . '</td>';
                echo '<td>' . htmlspecialchars($product->name) . '</td>';
                echo '<td>' . htmlspecialchars($product->category?->name ?? 'Sin categoría') . '</td>';
                echo '<td>' . htmlspecialchars($product->description ?? '') . '</td>';
                echo '<td>' . number_format($product->price, 2) . '</td>';
                echo '<td>' . ($product->is_active ? 'Activo' : 'Inactivo') . '</td>';
                echo '<td>' . $product->created_at->format('d/m/Y H:i') . '</td>';
                echo '</tr>';
            }

            echo '</table></body></html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    public function categoriesCSV(Request $request): StreamedResponse
    {
        $categories = Category::withCount('products')
            ->when($request->search, fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->when($request->status !== null && $request->status !== '', fn($q) =>
                $q->where('is_active', $request->status)
            )
            ->latest()
            ->get();

        $filename = 'categories_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($categories) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['ID', 'Nombre', 'Descripción', 'Productos', 'Estado', 'Creado']);

            foreach ($categories as $category) {
                fputcsv($handle, [
                    $category->id,
                    $category->name,
                    $category->description,
                    $category->products_count,
                    $category->is_active ? 'Activo' : 'Inactivo',
                    $category->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function categoriesExcel(Request $request): StreamedResponse
    {
        $categories = Category::withCount('products')
            ->when($request->search, fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->when($request->status !== null && $request->status !== '', fn($q) =>
                $q->where('is_active', $request->status)
            )
            ->latest()
            ->get();

        $filename = 'categories_' . now()->format('Y-m-d_His') . '.xls';

        return response()->streamDownload(function () use ($categories) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="UTF-8"></head>';
            echo '<body><table border="1">';

            echo '<tr style="background-color:#4472C4;color:white;font-weight:bold;">';
            echo '<th>ID</th><th>Nombre</th><th>Descripción</th><th>Productos</th><th>Estado</th><th>Creado</th>';
            echo '</tr>';

            foreach ($categories as $category) {
                echo '<tr>';
                echo '<td>' . $category->id . '</td>';
                echo '<td>' . htmlspecialchars($category->name) . '</td>';
                echo '<td>' . htmlspecialchars($category->description ?? '') . '</td>';
                echo '<td>' . $category->products_count . '</td>';
                echo '<td>' . ($category->is_active ? 'Activo' : 'Inactivo') . '</td>';
                echo '<td>' . $category->created_at->format('d/m/Y H:i') . '</td>';
                echo '</tr>';
            }

            echo '</table></body></html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }
}
