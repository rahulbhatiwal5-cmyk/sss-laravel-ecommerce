<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Return every descendant ID without recursing forever if legacy data contains a cycle.
     *
     * @return array<int, int>
     */
    public function descendantIds(): array
    {
        if (! $this->getKey()) {
            return [];
        }

        $childrenByParent = [];

        foreach (static::query()->get(['id', 'parent_id']) as $candidate) {
            if ($candidate->parent_id === null) {
                continue;
            }

            $childrenByParent[(string) $candidate->parent_id][] = (int) $candidate->getKey();
        }

        $queue = [(int) $this->getKey()];
        $visited = [(string) $this->getKey() => true];
        $descendantIds = [];

        for ($position = 0; $position < count($queue); $position++) {
            $parentId = $queue[$position];

            foreach ($childrenByParent[(string) $parentId] ?? [] as $childId) {
                $key = (string) $childId;

                if (isset($visited[$key])) {
                    continue;
                }

                $visited[$key] = true;
                $descendantIds[] = $childId;
                $queue[] = $childId;
            }
        }

        return $descendantIds;
    }
}
