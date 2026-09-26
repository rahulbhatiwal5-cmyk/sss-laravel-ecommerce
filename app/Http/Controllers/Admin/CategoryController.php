<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $search = trim((string) ($filters['search'] ?? ''));
        $status = $filters['status'] ?? null;

        $categories = Category::query()
            ->with('parent:id,name')
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($status, ['active', 'inactive'], true),
                fn ($query) => $query->where('is_active', $status === 'active'),
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('sss-admin.categories', compact('categories', 'search', 'status'));
    }

    public function create(): View
    {
        $parentCategories = $this->parentCategories();

        return view('sss-admin.categories.create', compact('parentCategories'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::query()->create($request->safe()->only([
            'name',
            'slug',
            'parent_id',
            'description',
            'is_active',
            'sort_order',
        ]));

        return to_route('admin.categories.index')->with('status', 'Category created successfully.');
    }

    public function edit(Category $category): View
    {
        $parentCategories = $this->parentCategories($category);

        return view('sss-admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->safe()->only([
            'name',
            'slug',
            'parent_id',
            'description',
            'is_active',
            'sort_order',
        ]));

        return to_route('admin.categories.index')->with('status', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $blockedMessage = DB::transaction(function () use ($category): ?string {
            $lockedCategory = Category::query()
                ->whereKey($category->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $hasProducts = $this->hasRelatedRecord($lockedCategory->products());
            $hasChildren = $this->hasRelatedRecord($lockedCategory->children());

            if ($hasProducts || $hasChildren) {
                return $this->deletionBlockedMessage($hasProducts, $hasChildren);
            }

            $lockedCategory->delete();

            return null;
        }, 3);

        if ($blockedMessage !== null) {
            return to_route('admin.categories.index')->with('error', $blockedMessage);
        }

        return to_route('admin.categories.index')->with('status', 'Category deleted successfully.');
    }

    private function parentCategories(?Category $category = null)
    {
        $query = Category::query()
            ->with('parent:id,name')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->orderBy('id');

        if ($category) {
            $query->whereNotIn('id', [
                $category->getKey(),
                ...$category->descendantIds(),
            ]);
        }

        return $query->get(['id', 'parent_id', 'name', 'slug']);
    }

    private function hasRelatedRecord(HasMany $relationship): bool
    {
        $related = $relationship->getRelated();

        if (in_array(SoftDeletes::class, \class_uses_recursive($related), true)) {
            $relationship->withTrashed();
        }

        return $relationship->lockForUpdate()->first(['id']) !== null;
    }

    private function deletionBlockedMessage(bool $hasProducts, bool $hasChildren): string
    {
        if ($hasProducts && $hasChildren) {
            return 'This category has products and child categories. Reassign them before deleting it.';
        }

        if ($hasProducts) {
            return 'This category has products. Reassign those products before deleting it.';
        }

        return 'This category has child categories. Reassign those child categories before deleting it.';
    }
}
