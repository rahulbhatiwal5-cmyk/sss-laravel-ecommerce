<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $search = trim((string) ($filters['search'] ?? ''));
        $categoryId = isset($filters['category_id']) ? (int) $filters['category_id'] : null;
        $status = $filters['status'] ?? null;

        $products = Product::query()
            ->with([
                'category:id,name',
                'brand:id,name',
                'media' => fn ($query) => $query->where('collection_name', 'main_image'),
            ])
            ->withCount('variants')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($categoryId !== null, fn ($query) => $query->where('category_id', $categoryId))
            ->when(
                in_array($status, ['active', 'inactive'], true),
                fn ($query) => $query->where('is_active', $status === 'active'),
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $products->getCollection()->each(function (Product $product): void {
            $product->setAttribute(
                'listing_image_url',
                $this->listingImageUrl($product->getFirstMedia('main_image')),
            );
        });

        $categories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name']);

        return view('sss-admin.products.index', compact(
            'products',
            'categories',
            'search',
            'categoryId',
            'status',
        ));
    }

    private function listingImageUrl(?Media $media): string
    {
        if ($media?->hasGeneratedConversion('thumb')) {
            return $media->getUrl('thumb');
        }

        return $media?->getUrl() ?? asset('sss-admin/images/product-placeholder.svg');
    }
}
