<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_customers_cannot_access_category_create_or_store_routes(): void
    {
        $payload = $this->validCategoryPayload();

        $this->get(route('admin.categories.create'))
            ->assertRedirect(route('admin.login'));

        $this->post(route('admin.categories.store'), $payload)
            ->assertRedirect(route('admin.login'));

        $customer = $this->makeUser(['role' => 'customer']);

        $this->actingAs($customer, 'web')
            ->get(route('admin.categories.create'))
            ->assertForbidden();

        $this->actingAs($customer, 'web')
            ->post(route('admin.categories.store'), $payload)
            ->assertForbidden();

        $this->assertDatabaseMissing('categories', ['slug' => $payload['slug']]);
    }

    public function test_active_admin_can_create_a_valid_root_category(): void
    {
        $admin = $this->makeUser();

        $response = $this->actingAs($admin, 'web')
            ->post(route('admin.categories.store'), $this->validCategoryPayload([
                'name' => 'Everyday Essentials',
                'slug' => 'everyday-essentials',
                'description' => 'The pieces that work every day.',
                'sort_order' => 3,
            ]));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Everyday Essentials',
            'slug' => 'everyday-essentials',
            'parent_id' => null,
            'description' => 'The pieces that work every day.',
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }

    public function test_active_admin_can_create_a_child_category(): void
    {
        $admin = $this->makeUser();
        $parent = $this->createCategory([
            'name' => 'Women',
            'slug' => 'women',
        ]);

        $this->actingAs($admin, 'web')
            ->post(route('admin.categories.store'), $this->validCategoryPayload([
                'name' => 'Dresses',
                'slug' => 'dresses',
                'parent_id' => $parent->id,
            ]))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Dresses',
            'slug' => 'dresses',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_slug_is_generated_when_blank_and_normalized_when_provided(): void
    {
        $admin = $this->makeUser();

        $this->actingAs($admin, 'web')
            ->post(route('admin.categories.store'), $this->validCategoryPayload([
                'name' => 'Soft Knitwear',
                'slug' => '',
            ]))
            ->assertRedirect(route('admin.categories.index'));

        $this->actingAs($admin, 'web')
            ->post(route('admin.categories.store'), $this->validCategoryPayload([
                'name' => 'Weekend Edit',
                'slug' => '  Weekend Edit / 2026!  ',
            ]))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['slug' => 'soft-knitwear']);
        $this->assertDatabaseHas('categories', ['slug' => 'weekend-edit-2026']);
    }

    public function test_invalid_and_duplicate_normalized_slugs_are_rejected(): void
    {
        $admin = $this->makeUser();
        $this->createCategory([
            'name' => 'Summer Edit',
            'slug' => 'summer-edit',
        ]);

        $this->actingAs($admin, 'web')
            ->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), $this->validCategoryPayload([
                'name' => 'No Usable Slug',
                'slug' => '---',
            ]))
            ->assertRedirect(route('admin.categories.create'))
            ->assertSessionHasErrors('slug');

        $this->actingAs($admin, 'web')
            ->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), $this->validCategoryPayload([
                'name' => 'Another Summer Edit',
                'slug' => ' Summer Edit! ',
            ]))
            ->assertRedirect(route('admin.categories.create'))
            ->assertSessionHasErrors('slug');

        $this->assertDatabaseCount('categories', 1);
    }

    public function test_category_creation_validates_name_parent_and_sort_order(): void
    {
        $admin = $this->makeUser();

        $this->actingAs($admin, 'web')
            ->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), $this->validCategoryPayload([
                'name' => '',
            ]))
            ->assertRedirect(route('admin.categories.create'))
            ->assertSessionHasErrors('name');

        $this->actingAs($admin, 'web')
            ->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), $this->validCategoryPayload([
                'parent_id' => 999999,
            ]))
            ->assertRedirect(route('admin.categories.create'))
            ->assertSessionHasErrors('parent_id');

        $this->actingAs($admin, 'web')
            ->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), $this->validCategoryPayload([
                'sort_order' => -1,
            ]))
            ->assertRedirect(route('admin.categories.create'))
            ->assertSessionHasErrors('sort_order');
    }

    public function test_unchecked_active_checkbox_is_saved_as_false(): void
    {
        $admin = $this->makeUser();
        $payload = $this->validCategoryPayload([
            'name' => 'Archived Collection',
            'slug' => 'archived-collection',
        ]);
        unset($payload['is_active']);

        $this->actingAs($admin, 'web')
            ->post(route('admin.categories.store'), $payload)
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'slug' => 'archived-collection',
            'is_active' => false,
        ]);
    }

    public function test_listing_shows_real_categories_with_parent_and_direct_product_count(): void
    {
        $admin = $this->makeUser();
        $parent = $this->createCategory([
            'name' => 'Outerwear',
            'slug' => 'outerwear',
        ]);
        $child = $this->createCategory([
            'name' => 'Jackets',
            'slug' => 'jackets',
            'parent_id' => $parent->id,
        ]);

        $this->createProduct($parent, 'Wool Coat', 'wool-coat');
        $this->createProduct($parent, 'Rain Coat', 'rain-coat');
        $this->createProduct($child, 'Bomber Jacket', 'bomber-jacket');

        $response = $this->actingAs($admin, 'web')
            ->get(route('admin.categories.index'));

        $response->assertOk()
            ->assertViewIs('sss-admin.categories')
            ->assertSeeText('Outerwear')
            ->assertSeeText('outerwear')
            ->assertSeeText('Jackets')
            ->assertSeeText('jackets');

        $categories = $this->categoriesFrom($response);
        $listedParent = $categories->getCollection()->firstWhere('id', $parent->id);
        $listedChild = $categories->getCollection()->firstWhere('id', $child->id);

        $this->assertNotNull($listedParent);
        $this->assertNotNull($listedChild);
        $this->assertTrue($listedParent->relationLoaded('parent'));
        $this->assertSame(2, $listedParent->products_count);
        $this->assertTrue($listedChild->relationLoaded('parent'));
        $this->assertSame($parent->id, $listedChild->parent?->id);
        $this->assertSame(1, $listedChild->products_count);
    }

    public function test_listing_searches_categories_by_name_and_slug(): void
    {
        $admin = $this->makeUser();
        $nameMatch = $this->createCategory([
            'name' => 'Summer Essentials',
            'slug' => 'summer-essentials',
        ]);
        $slugMatch = $this->createCategory([
            'name' => 'Warm Layers',
            'slug' => 'cozy-knits',
        ]);
        $other = $this->createCategory([
            'name' => 'Footwear',
            'slug' => 'footwear',
        ]);

        $nameSearch = $this->actingAs($admin, 'web')
            ->get(route('admin.categories.index', ['search' => 'summer']));
        $this->assertSame([$nameMatch->id], $this->categoriesFrom($nameSearch)->pluck('id')->all());

        $slugSearch = $this->actingAs($admin, 'web')
            ->get(route('admin.categories.index', ['search' => 'knits']));
        $this->assertSame([$slugMatch->id], $this->categoriesFrom($slugSearch)->pluck('id')->all());

        $this->assertNotSame([$other->id], $this->categoriesFrom($slugSearch)->pluck('id')->all());
    }

    public function test_listing_filters_status_paginates_and_preserves_filter_parameters(): void
    {
        $admin = $this->makeUser();
        $first = $this->createCategory([
            'name' => 'Pinned category',
            'slug' => 'pinned-category',
            'sort_order' => 0,
        ]);
        $activeCategories = [];

        for ($number = 1; $number <= 11; $number++) {
            $activeCategories[] = $this->createCategory([
                'name' => sprintf('Active category %02d', $number),
                'slug' => sprintf('active-category-%02d', $number),
                'sort_order' => 1,
            ]);
        }

        $inactive = $this->createCategory([
            'name' => 'Inactive category',
            'slug' => 'inactive-category',
            'is_active' => false,
        ]);

        $activeResponse = $this->actingAs($admin, 'web')
            ->get(route('admin.categories.index', ['status' => 'active']));
        $activePage = $this->categoriesFrom($activeResponse);

        $this->assertSame(12, $activePage->total());
        $this->assertSame(10, $activePage->perPage());
        $this->assertSame(10, $activePage->count());
        $this->assertSame(
            array_merge([$first->id], array_map(fn (Category $category) => $category->id, array_slice($activeCategories, 0, 9))),
            $activePage->pluck('id')->all(),
        );
        $activeResponse->assertSee('status=active', false);

        $secondPage = $this->actingAs($admin, 'web')
            ->get(route('admin.categories.index', ['status' => 'active', 'page' => 2]));
        $this->assertSame(
            array_map(fn (Category $category) => $category->id, array_slice($activeCategories, 9)),
            $this->categoriesFrom($secondPage)->pluck('id')->all(),
        );

        $inactiveResponse = $this->actingAs($admin, 'web')
            ->get(route('admin.categories.index', ['status' => 'inactive']));
        $this->assertSame([$inactive->id], $this->categoriesFrom($inactiveResponse)->pluck('id')->all());
    }

    public function test_guests_and_customers_cannot_access_category_edit_update_or_delete_routes(): void
    {
        $category = $this->createCategory();
        $payload = $this->validCategoryPayload([
            'name' => 'Updated category',
            'slug' => 'updated-category',
        ]);

        $this->get(route('admin.categories.edit', $category))
            ->assertRedirect(route('admin.login'));

        $this->put(route('admin.categories.update', $category), $payload)
            ->assertRedirect(route('admin.login'));

        $this->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.login'));

        $customer = $this->makeUser(['role' => 'customer']);

        $this->actingAs($customer, 'web')
            ->get(route('admin.categories.edit', $category))
            ->assertForbidden();

        $this->actingAs($customer, 'web')
            ->put(route('admin.categories.update', $category), $payload)
            ->assertForbidden();

        $this->actingAs($customer, 'web')
            ->delete(route('admin.categories.destroy', $category))
            ->assertForbidden();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'slug' => $category->slug,
        ]);
    }

    public function test_active_admin_can_edit_a_category_and_retain_its_own_slug(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory([
            'name' => 'Original collection',
            'slug' => 'original-collection',
            'description' => 'Old description',
            'sort_order' => 1,
        ]);

        $this->actingAs($admin, 'web')
            ->get(route('admin.categories.edit', $category))
            ->assertOk()
            ->assertSee('Original collection', false);

        $this->actingAs($admin, 'web')
            ->put(route('admin.categories.update', $category), $this->validCategoryPayload([
                'name' => 'Renamed collection',
                'slug' => 'original-collection',
                'description' => 'New description',
                'sort_order' => 4,
            ]))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Renamed collection',
            'slug' => 'original-collection',
            'description' => 'New description',
            'sort_order' => 4,
            'is_active' => true,
        ]);
    }

    public function test_clearing_a_category_slug_regenerates_it_from_the_new_name(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory([
            'name' => 'Original collection',
            'slug' => 'original-collection',
        ]);

        $this->actingAs($admin, 'web')
            ->put(route('admin.categories.update', $category), $this->validCategoryPayload([
                'name' => 'New Spring Edit',
                'slug' => '',
            ]))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Spring Edit',
            'slug' => 'new-spring-edit',
        ]);
    }

    public function test_category_update_rejects_a_slug_used_by_another_category(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory([
            'name' => 'Outerwear',
            'slug' => 'outerwear',
        ]);
        $existing = $this->createCategory([
            'name' => 'Footwear',
            'slug' => 'footwear',
        ]);

        $this->actingAs($admin, 'web')
            ->from(route('admin.categories.edit', $category))
            ->put(route('admin.categories.update', $category), $this->validCategoryPayload([
                'name' => 'Changed outerwear',
                'slug' => ' Footwear! ',
            ]))
            ->assertRedirect(route('admin.categories.edit', $category))
            ->assertSessionHasErrors('slug');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Outerwear',
            'slug' => 'outerwear',
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $existing->id,
            'slug' => 'footwear',
        ]);
    }

    public function test_category_update_rejects_self_and_deep_descendant_as_parent(): void
    {
        $admin = $this->makeUser();
        $root = $this->createCategory([
            'name' => 'Root',
            'slug' => 'root',
        ]);
        $child = $this->createCategory([
            'name' => 'Child',
            'slug' => 'child',
            'parent_id' => $root->id,
        ]);
        $grandchild = $this->createCategory([
            'name' => 'Grandchild',
            'slug' => 'grandchild',
            'parent_id' => $child->id,
        ]);

        $this->actingAs($admin, 'web')
            ->from(route('admin.categories.edit', $root))
            ->put(route('admin.categories.update', $root), $this->validCategoryPayload([
                'name' => $root->name,
                'slug' => $root->slug,
                'parent_id' => $root->id,
            ]))
            ->assertRedirect(route('admin.categories.edit', $root))
            ->assertSessionHasErrors('parent_id');

        $this->actingAs($admin, 'web')
            ->from(route('admin.categories.edit', $root))
            ->put(route('admin.categories.update', $root), $this->validCategoryPayload([
                'name' => $root->name,
                'slug' => $root->slug,
                'parent_id' => $grandchild->id,
            ]))
            ->assertRedirect(route('admin.categories.edit', $root))
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseHas('categories', [
            'id' => $root->id,
            'parent_id' => null,
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $child->id,
            'parent_id' => $root->id,
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $grandchild->id,
            'parent_id' => $child->id,
        ]);
    }

    public function test_edit_parent_options_exclude_the_category_and_all_its_descendants(): void
    {
        $admin = $this->makeUser();
        $root = $this->createCategory(['name' => 'Root', 'slug' => 'root']);
        $child = $this->createCategory(['name' => 'Child', 'slug' => 'child', 'parent_id' => $root->id]);
        $grandchild = $this->createCategory(['name' => 'Grandchild', 'slug' => 'grandchild', 'parent_id' => $child->id]);
        $unrelated = $this->createCategory(['name' => 'Unrelated', 'slug' => 'unrelated']);

        $response = $this->actingAs($admin, 'web')
            ->get(route('admin.categories.edit', $root))
            ->assertOk();

        $parentCategories = $response->viewData('parentCategories');

        $this->assertNotNull($parentCategories);
        $this->assertNotContains($root->id, $parentCategories->pluck('id')->all());
        $this->assertNotContains($child->id, $parentCategories->pluck('id')->all());
        $this->assertNotContains($grandchild->id, $parentCategories->pluck('id')->all());
        $this->assertContains($unrelated->id, $parentCategories->pluck('id')->all());
    }

    public function test_category_can_be_reparented_or_converted_back_to_a_root_category(): void
    {
        $admin = $this->makeUser();
        $firstParent = $this->createCategory(['name' => 'First parent', 'slug' => 'first-parent']);
        $secondParent = $this->createCategory(['name' => 'Second parent', 'slug' => 'second-parent']);
        $category = $this->createCategory([
            'name' => 'Moving category',
            'slug' => 'moving-category',
            'parent_id' => $firstParent->id,
        ]);

        $this->actingAs($admin, 'web')
            ->put(route('admin.categories.update', $category), $this->validCategoryPayload([
                'name' => $category->name,
                'slug' => $category->slug,
                'parent_id' => $secondParent->id,
            ]))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'parent_id' => $secondParent->id,
        ]);

        $this->actingAs($admin, 'web')
            ->put(route('admin.categories.update', $category), $this->validCategoryPayload([
                'name' => $category->name,
                'slug' => $category->slug,
                'parent_id' => '',
            ]))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'parent_id' => null,
        ]);
    }

    public function test_unchecked_active_checkbox_saves_false_when_updating_a_category(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory([
            'name' => 'Active category',
            'slug' => 'active-category',
            'is_active' => true,
        ]);
        $payload = $this->validCategoryPayload([
            'name' => $category->name,
            'slug' => $category->slug,
        ]);
        unset($payload['is_active']);

        $this->actingAs($admin, 'web')
            ->put(route('admin.categories.update', $category), $payload)
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'is_active' => false,
        ]);
    }

    public function test_an_active_admin_can_delete_an_empty_category(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory([
            'name' => 'Empty category',
            'slug' => 'empty-category',
        ]);

        $this->actingAs($admin, 'web')
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_deletion_is_blocked_when_products_or_children_exist_without_changing_related_records(): void
    {
        $admin = $this->makeUser();
        $categoryWithProduct = $this->createCategory([
            'name' => 'Product category',
            'slug' => 'product-category',
        ]);
        $product = $this->createProduct($categoryWithProduct, 'Protected product', 'protected-product');
        $categoryWithChild = $this->createCategory([
            'name' => 'Parent category',
            'slug' => 'parent-category',
        ]);
        $child = $this->createCategory([
            'name' => 'Protected child',
            'slug' => 'protected-child',
            'parent_id' => $categoryWithChild->id,
        ]);

        $this->actingAs($admin, 'web')
            ->from(route('admin.categories.index'))
            ->delete(route('admin.categories.destroy', $categoryWithProduct))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error');

        $this->actingAs($admin, 'web')
            ->from(route('admin.categories.index'))
            ->delete(route('admin.categories.destroy', $categoryWithChild))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $categoryWithProduct->id]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'category_id' => $categoryWithProduct->id,
        ]);
        $this->assertDatabaseHas('categories', ['id' => $categoryWithChild->id]);
        $this->assertDatabaseHas('categories', [
            'id' => $child->id,
            'parent_id' => $categoryWithChild->id,
        ]);
    }

    public function test_missing_categories_return_not_found_for_edit_update_and_delete(): void
    {
        $admin = $this->makeUser();
        $missingCategory = 999999;

        $this->actingAs($admin, 'web')
            ->get(route('admin.categories.edit', $missingCategory))
            ->assertNotFound();

        $this->actingAs($admin, 'web')
            ->put(route('admin.categories.update', $missingCategory), $this->validCategoryPayload())
            ->assertNotFound();

        $this->actingAs($admin, 'web')
            ->delete(route('admin.categories.destroy', $missingCategory))
            ->assertNotFound();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createCategory(array $attributes = []): Category
    {
        return Category::query()->create(array_merge([
            'name' => 'Category '.fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'is_active' => true,
            'sort_order' => 0,
        ], $attributes));
    }

    private function createProduct(Category $category, string $name, string $slug): Product
    {
        return Product::query()->create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'price' => 99.99,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validCategoryPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Category',
            'slug' => 'new-category',
            'parent_id' => null,
            'description' => 'A short category description.',
            'is_active' => '1',
            'sort_order' => 0,
        ], $overrides);
    }

    private function categoriesFrom(TestResponse $response): LengthAwarePaginator
    {
        $categories = $response->viewData('categories');

        $this->assertInstanceOf(LengthAwarePaginator::class, $categories);

        return $categories;
    }
}
