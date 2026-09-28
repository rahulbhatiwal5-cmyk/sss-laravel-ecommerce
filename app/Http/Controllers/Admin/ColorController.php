<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveColorRequest;
use App\Models\Color;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ColorController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $search = trim((string) ($filters['search'] ?? ''));
        $status = $filters['status'] ?? null;

        $colors = Color::query()
            ->withCount('variants')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
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

        return view('sss-admin.colors.index', compact('colors', 'search', 'status'));
    }

    public function create(): View
    {
        return view('sss-admin.colors.create');
    }

    public function store(SaveColorRequest $request): RedirectResponse
    {
        Color::query()->create($request->safe()->only([
            'name',
            'code',
            'is_active',
        ]));

        return to_route('admin.colors.index')->with('status', 'Color created successfully.');
    }

    public function edit(Color $color): View
    {
        return view('sss-admin.colors.edit', compact('color'));
    }

    public function update(SaveColorRequest $request, Color $color): RedirectResponse
    {
        $color->update($request->safe()->only([
            'name',
            'code',
            'is_active',
        ]));

        return to_route('admin.colors.index')->with('status', 'Color updated successfully.');
    }

    public function destroy(Color $color): RedirectResponse
    {
        try {
            $variantCount = DB::transaction(function () use ($color): int {
                $lockedColor = Color::query()
                    ->whereKey($color->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $variants = $lockedColor->variants()
                    ->lockForUpdate()
                    ->get(['id']);

                if ($variants->isNotEmpty()) {
                    return $variants->count();
                }

                $lockedColor->delete();

                return 0;
            }, 3);
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return to_route('admin.colors.index')->with(
                'error',
                'The color could not be deleted. Please try again.',
            );
        }

        if ($variantCount > 0) {
            return to_route('admin.colors.index')->with(
                'error',
                "This color is used by {$variantCount} product variant(s). Reassign those variants before deleting it.",
            );
        }

        return to_route('admin.colors.index')->with('status', 'Color deleted successfully.');
    }
}
