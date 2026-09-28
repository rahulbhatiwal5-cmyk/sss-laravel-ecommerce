<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveSizeRequest;
use App\Models\Size;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class SizeController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $search = trim((string) ($filters['search'] ?? ''));
        $status = $filters['status'] ?? null;

        $sizes = Size::query()
            ->withCount('variants')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when(
                in_array($status, ['active', 'inactive'], true),
                fn ($query) => $query->where('is_active', $status === 'active'),
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('sss-admin.sizes.index', compact('sizes', 'search', 'status'));
    }

    public function create(): View
    {
        return view('sss-admin.sizes.create');
    }

    public function store(SaveSizeRequest $request): RedirectResponse
    {
        Size::query()->create($request->safe()->only([
            'name',
            'sort_order',
            'is_active',
        ]));

        return to_route('admin.sizes.index')->with('status', 'Size created successfully.');
    }

    public function edit(Size $size): View
    {
        return view('sss-admin.sizes.edit', compact('size'));
    }

    public function update(SaveSizeRequest $request, Size $size): RedirectResponse
    {
        $size->update($request->safe()->only([
            'name',
            'sort_order',
            'is_active',
        ]));

        return to_route('admin.sizes.index')->with('status', 'Size updated successfully.');
    }

    public function destroy(Size $size): RedirectResponse
    {
        try {
            $variantCount = DB::transaction(function () use ($size): int {
                $lockedSize = Size::query()
                    ->whereKey($size->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $variants = $lockedSize->variants()
                    ->lockForUpdate()
                    ->get(['id']);

                if ($variants->isNotEmpty()) {
                    return $variants->count();
                }

                $lockedSize->delete();

                return 0;
            }, 3);
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return to_route('admin.sizes.index')->with(
                'error',
                'The size could not be deleted. Please try again.',
            );
        }

        if ($variantCount > 0) {
            return to_route('admin.sizes.index')->with(
                'error',
                "This size is used by {$variantCount} product variant(s). Reassign those variants before deleting it.",
            );
        }

        return to_route('admin.sizes.index')->with('status', 'Size deleted successfully.');
    }
}
