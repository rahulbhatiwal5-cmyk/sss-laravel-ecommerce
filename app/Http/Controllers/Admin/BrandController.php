<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveBrandRequest;
use App\Models\Brand;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class BrandController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $search = trim((string) ($filters['search'] ?? ''));
        $status = $filters['status'] ?? null;

        $brands = Brand::query()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($status, ['active', 'inactive'], true),
                fn ($query) => $query->where('is_active', $status === 'active'),
            )
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('sss-admin.brands.index', compact('brands', 'search', 'status'));
    }

    public function create(): View
    {
        return view('sss-admin.brands.create');
    }

    public function store(SaveBrandRequest $request): RedirectResponse
    {
        Brand::query()->create($request->safe()->only([
            'name',
            'slug',
            'logo',
            'description',
            'is_active',
        ]));

        return to_route('admin.brands.index')->with('status', 'Brand created successfully.');
    }

    public function edit(Brand $brand): View
    {
        return view('sss-admin.brands.edit', compact('brand'));
    }

    public function update(SaveBrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->update($request->safe()->only([
            'name',
            'slug',
            'logo',
            'description',
            'is_active',
        ]));

        return to_route('admin.brands.index')->with('status', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        try {
            $productCount = DB::transaction(function () use ($brand): int {
                $lockedBrand = Brand::query()
                    ->whereKey($brand->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $products = $lockedBrand->products()
                    ->lockForUpdate()
                    ->get(['id']);

                if ($products->isNotEmpty()) {
                    return $products->count();
                }

                $lockedBrand->delete();

                return 0;
            }, 3);
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return to_route('admin.brands.index')->with(
                'error',
                'The brand could not be deleted. Please try again.',
            );
        }

        if ($productCount > 0) {
            return to_route('admin.brands.index')->with(
                'error',
                "This brand is assigned to {$productCount} product(s). Reassign those products before deleting it.",
            );
        }

        return to_route('admin.brands.index')->with('status', 'Brand deleted successfully.');
    }
}
