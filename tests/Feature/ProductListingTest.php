<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Testing\TestResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class ProductListingTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    public function test_guests_and_customers_cannot_access_the_product_listing(): void
    {
        $this->get(route('admin.products.index'))
            ->assertRedirect(route('admin.login'));

        $customer = $this->makeUser(['role' => 'customer']);

        $this->actingAs($customer, 'web')
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_an_active_admin_sees_a_helpful_empty_state_and_live_data_label(): void
    {
        $admin = $this->makeUser();

        $response = $this->actingAs($admin, 'web')
            ->get(route('admin.products.index'));

        $response->assertOk()
            ->assertViewIs('sss-admin.products.index')
            ->assertSeeText('Live product data')
            ->assertSeeText('No products');

        $products = $this->productsFrom($response);

        $this->assertSame(0, $products->total());
        $this->assertSame(0, $products->count());
    }

    public function test_listing_shows_real_product_details_with_loaded_relationships_and_variant_count(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory(['name' => 'Outerwear', 'slug' => 'outerwear']);
        $brand = $this->createBrand(['name' => 'North Studio', 'slug' => 'north-studio']);
        $product = $this->createProduct($category, $brand, [
            'name' => 'Wool Overcoat',
            'slug' => 'wool-overcoat',
            'price' => 1299.00,
            'sale_price' => 999.00,
        ]);
        $unbrandedProduct = $this->createProduct($category, null, [
            'name' => 'Classic Scarf',
            'slug' => 'classic-scarf',
            'is_active' => false,
        ]);

        $this->createVariant($product);
        $this->createVariant($product);

        $response = $this->actingAs($admin, 'web')
            ->get(route('admin.products.index'));

        $response->assertOk()
            ->assertSeeText('Wool Overcoat')
            ->assertSeeText('wool-overcoat')
            ->assertSeeText('Outerwear')
            ->assertSeeText('North Studio')
            ->assertSeeText('1,299')
            ->assertSeeText('999')
            ->assertSeeText('Active')
            ->assertSeeText('Inactive')
            ->assertSeeText('—');

        $listedProduct = $this->productsFrom($response)->getCollection()->firstWhere('id', $product->id);
        $listedUnbrandedProduct = $this->productsFrom($response)->getCollection()->firstWhere('id', $unbrandedProduct->id);

        $this->assertNotNull($listedProduct);
        $this->assertTrue($listedProduct->relationLoaded('category'));
        $this->assertTrue($listedProduct->relationLoaded('brand'));
        $this->assertTrue($listedProduct->relationLoaded('media'));
        $this->assertSame($category->id, $listedProduct->category?->id);
        $this->assertSame($brand->id, $listedProduct->brand?->id);
        $this->assertSame(2, $listedProduct->variants_count);
        $this->assertNotNull($listedUnbrandedProduct);
        $this->assertNull($listedUnbrandedProduct->brand);
    }

    public function test_listing_searches_name_and_slug_and_filters_category_and_active_status(): void
    {
        $admin = $this->makeUser();
        $outerwear = $this->createCategory(['name' => 'Outerwear', 'slug' => 'outerwear']);
        $accessories = $this->createCategory(['name' => 'Accessories', 'slug' => 'accessories']);
        $nameMatch = $this->createProduct($outerwear, null, [
            'name' => 'Linen Overshirt',
            'slug' => 'linen-overshirt',
        ]);
        $slugMatch = $this->createProduct($outerwear, null, [
            'name' => 'Warm Layers',
            'slug' => 'cozy-knit',
        ]);
        $inactive = $this->createProduct($outerwear, null, [
            'name' => 'Archived Coat',
            'slug' => 'archived-coat',
            'is_active' => false,
        ]);
        $otherCategory = $this->createProduct($accessories, null, [
            'name' => 'Linen Tote',
            'slug' => 'linen-tote',
        ]);

        $nameSearch = $this->actingAs($admin, 'web')
            ->get(route('admin.products.index', ['search' => 'overshirt']));
        $this->assertSame([$nameMatch->id], $this->productsFrom($nameSearch)->pluck('id')->all());

        $slugSearch = $this->actingAs($admin, 'web')
            ->get(route('admin.products.index', ['search' => 'cozy-knit']));
        $this->assertSame([$slugMatch->id], $this->productsFrom($slugSearch)->pluck('id')->all());

        $categoryFilter = $this->actingAs($admin, 'web')
            ->get(route('admin.products.index', ['category_id' => $accessories->id]));
        $this->assertSame([$otherCategory->id], $this->productsFrom($categoryFilter)->pluck('id')->all());

        $activeFilter = $this->actingAs($admin, 'web')
            ->get(route('admin.products.index', ['status' => 'active']));
        $this->assertEqualsCanonicalizing(
            [$nameMatch->id, $slugMatch->id, $otherCategory->id],
            $this->productsFrom($activeFilter)->pluck('id')->all(),
        );

        $inactiveFilter = $this->actingAs($admin, 'web')
            ->get(route('admin.products.index', ['status' => 'inactive']));
        $this->assertSame([$inactive->id], $this->productsFrom($inactiveFilter)->pluck('id')->all());
    }

    public function test_listing_orders_newest_first_then_id_and_preserves_filters_through_pagination(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory(['name' => 'Essentials', 'slug' => 'essentials']);
        $products = [];

        for ($number = 1; $number <= 11; $number++) {
            $products[] = $this->createProduct($category, null, [
                'name' => sprintf('Paged piece %02d', $number),
                'slug' => sprintf('paged-piece-%02d', $number),
            ]);
        }

        $query = [
            'search' => 'Paged piece',
            'category_id' => $category->id,
            'status' => 'active',
        ];
        $firstPageResponse = $this->actingAs($admin, 'web')
            ->get(route('admin.products.index', $query));
        $firstPage = $this->productsFrom($firstPageResponse);

        $this->assertSame(11, $firstPage->total());
        $this->assertSame(10, $firstPage->perPage());
        $this->assertSame(10, $firstPage->count());
        $this->assertSame(
            array_map(fn (Product $product) => $product->id, array_reverse(array_slice($products, 1))),
            $firstPage->pluck('id')->all(),
        );

        $secondPageUrl = $firstPage->url(2);
        $this->assertStringContainsString('search=Paged%20piece', $secondPageUrl);
        $this->assertStringContainsString('category_id='.$category->id, $secondPageUrl);
        $this->assertStringContainsString('status=active', $secondPageUrl);

        $secondPageResponse = $this->actingAs($admin, 'web')
            ->get(route('admin.products.index', [...$query, 'page' => 2]));
        $this->assertSame([$products[0]->id], $this->productsFrom($secondPageResponse)->pluck('id')->all());
    }

    public function test_listing_uses_an_existing_thumbnail_then_original_then_local_placeholder_without_generating_conversions(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory();
        $withThumb = $this->createProduct($category, null, [
            'name' => 'Thumbnail product',
            'slug' => 'thumbnail-product',
        ]);
        $originalOnly = $this->createProduct($category, null, [
            'name' => 'Original product',
            'slug' => 'original-product',
        ]);
        $withoutImage = $this->createProduct($category, null, [
            'name' => 'Placeholder product',
            'slug' => 'placeholder-product',
        ]);

        // These pre-existing media rows deliberately avoid addMedia(), image processing, and queues.
        $thumbnailMedia = $this->createMainImage($withThumb, 'thumbnail-product.jpg', true);
        $originalMedia = $this->createMainImage($originalOnly, 'original-product.jpg', false);

        $response = $this->actingAs($admin, 'web')
            ->get(route('admin.products.index'));
        $listedProducts = $this->productsFrom($response)->getCollection()->keyBy('id');

        $thumbnailUrl = $thumbnailMedia->getUrl('thumb');
        $originalUrl = $originalMedia->getUrl();
        $placeholderUrl = asset('sss-admin/images/product-placeholder.svg');

        $response->assertSee($thumbnailUrl, false)
            ->assertSee($originalUrl, false)
            ->assertSee($placeholderUrl, false);

        $this->assertSame(
            $thumbnailUrl,
            $listedProducts->get($withThumb->id)->listing_image_url,
        );
        $this->assertSame(
            $originalUrl,
            $listedProducts->get($originalOnly->id)->listing_image_url,
        );
        $this->assertSame(
            $placeholderUrl,
            $listedProducts->get($withoutImage->id)->listing_image_url,
        );
        $this->assertTrue($thumbnailMedia->fresh()->hasGeneratedConversion('thumb'));
        $this->assertFalse($originalMedia->fresh()->hasGeneratedConversion('thumb'));
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
        $number = ++$this->sequence;

        return Category::query()->create(array_merge([
            'name' => 'Category '.$number,
            'slug' => 'category-'.$number,
            'is_active' => true,
            'sort_order' => 0,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createBrand(array $attributes = []): Brand
    {
        $number = ++$this->sequence;

        return Brand::query()->create(array_merge([
            'name' => 'Brand '.$number,
            'slug' => 'brand-'.$number,
            'is_active' => true,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createProduct(Category $category, ?Brand $brand, array $attributes = []): Product
    {
        $number = ++$this->sequence;

        return Product::query()->create(array_merge([
            'category_id' => $category->id,
            'brand_id' => $brand?->id,
            'name' => 'Product '.$number,
            'slug' => 'product-'.$number,
            'price' => 100.00,
            'is_active' => true,
        ], $attributes));
    }

    private function createVariant(Product $product): ProductVariant
    {
        $number = ++$this->sequence;

        return ProductVariant::query()->create([
            'product_id' => $product->id,
            'sku' => 'VARIANT-'.$number,
            'stock' => 0,
            'is_active' => true,
        ]);
    }

    private function createMainImage(Product $product, string $fileName, bool $hasThumb): Media
    {
        return Media::query()->create([
            'model_type' => Product::class,
            'model_id' => $product->id,
            'collection_name' => 'main_image',
            'name' => pathinfo($fileName, PATHINFO_FILENAME),
            'file_name' => $fileName,
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'conversions_disk' => 'public',
            'size' => 1,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => ['thumb' => $hasThumb],
            'responsive_images' => [],
            'order_column' => 1,
        ]);
    }

    private function productsFrom(TestResponse $response): LengthAwarePaginator
    {
        $products = $response->viewData('products');

        $this->assertInstanceOf(LengthAwarePaginator::class, $products);

        return $products;
    }
}
