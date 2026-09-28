<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\ProductMediaUploader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class ProductCreationTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        config()->set([
            'media-library.disk_name' => 'public',
            'media-library.conversions_disk_name' => 'public',
            'media-library.queue_conversions_by_default' => true,
            'media-library.queue_conversions_after_database_commit' => true,
        ]);
    }

    public function test_guests_and_customers_cannot_access_product_create_or_store_routes(): void
    {
        $category = $this->createCategory();
        $payload = $this->validPayload($category);

        $this->get(route('admin.products.create'))
            ->assertRedirect(route('admin.login'));

        $this->post(route('admin.products.store'), $payload)
            ->assertRedirect(route('admin.login'));

        $customer = $this->makeUser(['role' => 'customer']);

        $this->actingAs($customer, 'web')
            ->get(route('admin.products.create'))
            ->assertForbidden();

        $this->actingAs($customer, 'web')
            ->post(route('admin.products.store'), $this->validPayload($category))
            ->assertForbidden();

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_variants', 0);
        $this->assertDatabaseCount('media', 0);
    }

    public function test_create_page_explains_that_a_category_is_required_and_a_crafted_post_is_rejected_when_none_exist(): void
    {
        $admin = $this->makeUser();

        $this->actingAs($admin, 'web')
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee(route('admin.categories.create'), false);

        $this->actingAs($admin, 'web')
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $this->validPayload())
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('category_id');

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_variants', 0);
        $this->assertDatabaseCount('media', 0);
    }

    public function test_an_active_admin_can_create_a_simple_product_with_one_default_variant_and_spatie_media(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory(['name' => 'Outerwear', 'slug' => 'outerwear']);
        $brand = $this->createBrand(['name' => 'North Studio', 'slug' => 'north-studio']);

        $response = $this->actingAs($admin, 'web')
            ->post(route('admin.products.store'), $this->validPayload($category, [
                'brand_id' => $brand->id,
                'name' => '  Wool Overcoat  ',
                'slug' => '',
                'short_description' => 'A warm daily layer.',
                'description' => 'A longer product description.',
                'material' => 'Wool blend',
                'care_instructions' => 'Dry clean only',
                'gender' => 'Unisex',
                'price' => '1499.50',
                'sale_price' => '1099.00',
                'variant_sku' => 'WOOL-OVERCOAT-DEFAULT',
                'stock' => '12',
                'low_stock_limit' => '3',
                'gallery' => [
                    $this->image('overcoat-gallery-one.jpg'),
                    $this->image('overcoat-gallery-two.png'),
                ],
            ]));

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('slug', 'wool-overcoat')->firstOrFail();
        $variant = $product->variants()->sole();

        $this->assertSame($category->id, $product->category_id);
        $this->assertSame($brand->id, $product->brand_id);
        $this->assertSame('Wool Overcoat', $product->name);
        $this->assertNull($product->sku);
        $this->assertSame('A warm daily layer.', $product->short_description);
        $this->assertSame('A longer product description.', $product->description);
        $this->assertSame('Wool blend', $product->material);
        $this->assertSame('Dry clean only', $product->care_instructions);
        $this->assertSame('Unisex', $product->gender);
        $this->assertSame('1499.50', $product->price);
        $this->assertSame('1099.00', $product->sale_price);
        $this->assertTrue($product->is_active);
        $this->assertTrue($product->is_featured);
        $this->assertNotNull($product->published_at);

        $this->assertSame($product->id, $variant->product_id);
        $this->assertNull($variant->color_id);
        $this->assertNull($variant->size_id);
        $this->assertSame('WOOL-OVERCOAT-DEFAULT', $variant->sku);
        $this->assertNull($variant->price);
        $this->assertNull($variant->sale_price);
        $this->assertSame(12, $variant->stock);
        $this->assertSame(3, $variant->low_stock_limit);
        $this->assertTrue($variant->is_active);
        $this->assertSame(1, $product->variants()->count());

        $product->load('media');
        $this->assertCount(1, $product->getMedia('main_image'));
        $this->assertCount(2, $product->getMedia('gallery'));
        $this->assertSame(['gallery', 'gallery', 'main_image'], $product->media->pluck('collection_name')->sort()->values()->all());
        $this->assertDatabaseCount('media', 3);
        $this->assertDatabaseCount('product_images', 0);
    }

    public function test_unchecked_product_checkboxes_create_an_inactive_unfeatured_unpublished_product(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory();
        $payload = $this->validPayload($category, [
            'name' => 'Hidden shirt',
            'slug' => 'hidden-shirt',
            'variant_sku' => 'HIDDEN-SHIRT-DEFAULT',
        ]);
        unset($payload['is_active'], $payload['is_featured']);

        $this->actingAs($admin, 'web')
            ->post(route('admin.products.store'), $payload)
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('slug', 'hidden-shirt')->firstOrFail();

        $this->assertFalse($product->is_active);
        $this->assertFalse($product->is_featured);
        $this->assertNull($product->published_at);
        $this->assertTrue($product->variants()->sole()->is_active);
    }

    public function test_product_creation_rejects_invalid_category_and_brand_ids(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory();

        $this->actingAs($admin, 'web')
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $this->validPayload($category, [
                'category_id' => 999999,
            ]))
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('category_id');

        $this->actingAs($admin, 'web')
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $this->validPayload($category, [
                'brand_id' => 999999,
            ]))
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('brand_id');

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_variants', 0);
        $this->assertDatabaseCount('media', 0);
    }

    public function test_product_creation_rejects_duplicate_normalized_slug_and_default_variant_sku(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory();
        $existingProduct = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Existing product',
            'slug' => 'existing-product',
            'price' => 100,
            'is_active' => true,
        ]);
        ProductVariant::query()->create([
            'product_id' => $existingProduct->id,
            'sku' => 'EXISTING-DEFAULT-SKU',
            'stock' => 0,
            'low_stock_limit' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'web')
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $this->validPayload($category, [
                'name' => 'Another product',
                'slug' => ' Existing Product! ',
            ]))
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('slug');

        $this->actingAs($admin, 'web')
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $this->validPayload($category, [
                'name' => 'A different product',
                'slug' => 'a-different-product',
                'variant_sku' => 'EXISTING-DEFAULT-SKU',
            ]))
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('variant_sku');

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('product_variants', 1);
    }

    public function test_product_creation_rejects_an_empty_slug_generated_from_an_invalid_name(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory();

        $this->actingAs($admin, 'web')
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $this->validPayload($category, [
                'name' => '!!!',
                'slug' => '',
            ]))
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('slug');

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_variants', 0);
        $this->assertDatabaseCount('media', 0);
    }

    public function test_product_creation_rejects_invalid_prices_and_decimal_precision(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory();

        foreach ([
            ['price' => '0', 'error' => 'price'],
            ['price' => '12.345', 'error' => 'price'],
            ['price' => '100000000.00', 'error' => 'price'],
            ['price' => '100.00', 'sale_price' => '100.00', 'error' => 'sale_price'],
            ['price' => '100.00', 'sale_price' => '101.00', 'error' => 'sale_price'],
            ['price' => '100.00', 'sale_price' => '99.999', 'error' => 'sale_price'],
        ] as $case) {
            $payload = $this->validPayload($category, $case);
            unset($payload['error']);

            $this->actingAs($admin, 'web')
                ->from(route('admin.products.create'))
                ->post(route('admin.products.store'), $payload)
                ->assertRedirect(route('admin.products.create'))
                ->assertSessionHasErrors($case['error']);
        }

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_variants', 0);
    }

    public function test_product_creation_rejects_negative_and_out_of_range_default_variant_inventory(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory();

        foreach ([
            ['stock' => '-1', 'error' => 'stock'],
            ['low_stock_limit' => '-1', 'error' => 'low_stock_limit'],
            ['stock' => '4294967296', 'error' => 'stock'],
            ['low_stock_limit' => '4294967296', 'error' => 'low_stock_limit'],
        ] as $case) {
            $payload = $this->validPayload($category, $case);
            unset($payload['error']);

            $this->actingAs($admin, 'web')
                ->from(route('admin.products.create'))
                ->post(route('admin.products.store'), $payload)
                ->assertRedirect(route('admin.products.create'))
                ->assertSessionHasErrors($case['error']);
        }

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_variants', 0);
    }

    public function test_product_creation_requires_a_valid_image_with_allowed_type_size_and_dimensions(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory();

        $missingMainImage = $this->validPayload($category);
        unset($missingMainImage['main_image']);

        $cases = [
            $missingMainImage,
            $this->validPayload($category, [
                'main_image' => UploadedFile::fake()->create('not-an-image.jpg', 20, 'image/jpeg'),
            ]),
            $this->validPayload($category, [
                'main_image' => UploadedFile::fake()->create('main.gif', 20, 'image/gif'),
            ]),
            $this->validPayload($category, [
                'main_image' => $this->image('main.jpg', 800, 1000, 2049),
            ]),
            $this->validPayload($category, [
                'main_image' => $this->image('main.jpg', 99, 100),
            ]),
            $this->validPayload($category, [
                'main_image' => $this->image('main.jpg', 6001, 100),
            ]),
        ];

        foreach ($cases as $payload) {
            $this->actingAs($admin, 'web')
                ->from(route('admin.products.create'))
                ->post(route('admin.products.store'), $payload)
                ->assertRedirect(route('admin.products.create'))
                ->assertSessionHasErrors('main_image');
        }

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('media', 0);
    }

    public function test_product_creation_rejects_too_many_or_invalid_gallery_images(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory();

        $tooManyGalleryImages = [];
        for ($number = 1; $number <= 6; $number++) {
            $tooManyGalleryImages[] = $this->image("gallery-{$number}.jpg");
        }

        $this->actingAs($admin, 'web')
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $this->validPayload($category, [
                'gallery' => $tooManyGalleryImages,
            ]))
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('gallery');

        $this->actingAs($admin, 'web')
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $this->validPayload($category, [
                'gallery' => [UploadedFile::fake()->create('not-a-gallery-image.jpg', 20, 'image/jpeg')],
            ]))
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('gallery.0');

        $this->actingAs($admin, 'web')
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $this->validPayload($category, [
                'gallery' => [$this->image('oversized-gallery.jpg', 800, 1000, 2049)],
            ]))
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('gallery.0');

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('media', 0);
    }

    public function test_upload_failure_rolls_back_models_media_rows_and_written_files(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory();

        $this->app->bind(ProductMediaUploader::class, ThrowOnSecondMediaAddProductMediaUploader::class);

        $this->actingAs($admin, 'web')
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $this->validPayload($category, [
                'gallery' => [$this->image('will-throw-on-gallery.jpg')],
            ]))
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('main_image')
            ->assertSessionHas('error');

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_variants', 0);
        $this->assertDatabaseCount('media', 0);
        $this->assertDatabaseCount('product_images', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
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
            'name' => "Category {$number}",
            'slug' => "category-{$number}",
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
            'name' => "Brand {$number}",
            'slug' => "brand-{$number}",
            'is_active' => true,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(?Category $category = null, array $overrides = []): array
    {
        $number = ++$this->sequence;

        return array_merge([
            'category_id' => $category?->id,
            'brand_id' => '',
            'name' => "Simple product {$number}",
            'slug' => "simple-product-{$number}",
            'short_description' => 'A short description.',
            'description' => 'A full product description.',
            'material' => 'Cotton',
            'care_instructions' => 'Machine wash cold.',
            'gender' => 'Unisex',
            'price' => '1499.50',
            'sale_price' => '1099.00',
            'is_active' => '1',
            'is_featured' => '1',
            'variant_sku' => "SIMPLE-{$number}-DEFAULT",
            'stock' => '10',
            'low_stock_limit' => '2',
            'main_image' => $this->image("main-{$number}.jpg"),
            'gallery' => [],
        ], $overrides);
    }

    private function image(string $name, int $width = 800, int $height = 1000, int $kilobytes = 100): UploadedFile
    {
        return UploadedFile::fake()->image($name, $width, $height)->size($kilobytes);
    }
}

class ThrowOnSecondMediaAddProductMediaUploader extends ProductMediaUploader
{
    private int $additions = 0;

    protected function add(Product $product, UploadedFile $file, string $collection): Media
    {
        $this->additions++;

        if ($this->additions === 2) {
            throw new RuntimeException('Simulated gallery upload failure.');
        }

        return parent::add($product, $file, $collection);
    }
}
