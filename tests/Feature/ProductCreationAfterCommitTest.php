<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob;
use Tests\TestCase;

class ProductCreationAfterCommitTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        config()->set([
            'queue.default' => 'database',
            'queue.connections.database.connection' => null,
            'queue.connections.database.table' => 'jobs',
            'queue.connections.database.queue' => 'default',
            'queue.connections.database.after_commit' => false,
            'media-library.disk_name' => 'public',
            'media-library.conversions_disk_name' => 'public',
            'media-library.queue_connection_name' => 'database',
            'media-library.queue_conversions_by_default' => true,
            'media-library.queue_conversions_after_database_commit' => true,
        ]);

        Queue::fakeExcept(PerformConversionsJob::class);

        // This class deliberately has no RefreshDatabase-owned outer transaction.
        // The manual transaction in the test is therefore the transaction that
        // governs the product, media, and conversion dispatch.
        DB::connection()->setTransactionManager($this->app->make('db.transactions'));
    }

    protected function tearDown(): void
    {
        $connection = DB::connection();

        while ($connection->transactionLevel() > 0) {
            $connection->rollBack();
        }

        // beginDatabaseTransaction() is intentionally disabled below so that a
        // committed test cannot leak rows into the shared in-memory test PDO.
        foreach ([
            'media',
            'product_images',
            'product_variants',
            'products',
            'brands',
            'categories',
            'users',
            'jobs',
        ] as $table) {
            DB::table($table)->delete();
        }

        $disk = Storage::disk('public');
        $disk->delete($disk->allFiles());

        parent::tearDown();
    }

    public function test_product_conversion_job_is_only_queued_after_the_outer_transaction_commits(): void
    {
        $admin = $this->makeUser();
        $category = $this->createCategory();
        $connection = DB::connection();

        $connection->beginTransaction();

        try {
            $this->actingAs($admin, 'web')
                ->post(route('admin.products.store'), $this->validPayload($category))
                ->assertRedirect(route('admin.products.index'));

            $this->assertDatabaseCount('products', 1);
            $this->assertDatabaseCount('media', 1);
            $this->assertDatabaseCount('jobs', 0);

            $connection->commit();
        } catch (\Throwable $exception) {
            while ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }

            throw $exception;
        }

        $this->assertDatabaseCount('jobs', 1);

        /** @var array{displayName: string, data: array{commandName: string}} $payload */
        $payload = json_decode((string) DB::table('jobs')->value('payload'), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(PerformConversionsJob::class, $payload['displayName']);
        $this->assertSame(PerformConversionsJob::class, $payload['data']['commandName']);
    }

    /**
     * RefreshDatabase normally starts a test-wide transaction. It would prevent
     * us from observing a real root commit, so this test owns its transaction
     * explicitly and tearDown removes every row it created from SQLite memory.
     */
    public function beginDatabaseTransaction(): void
    {
        // Intentionally empty; see tearDown().
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
     * @return array<string, mixed>
     */
    private function validPayload(Category $category): array
    {
        $number = ++$this->sequence;

        return [
            'category_id' => $category->id,
            'brand_id' => '',
            'name' => "Queued product {$number}",
            'slug' => "queued-product-{$number}",
            'short_description' => 'Queued product short description.',
            'description' => 'Queued product description.',
            'material' => 'Cotton',
            'care_instructions' => 'Machine wash cold.',
            'gender' => 'Unisex',
            'price' => '99.99',
            'sale_price' => '',
            'is_active' => '1',
            'is_featured' => '0',
            'variant_sku' => "QUEUED-{$number}-DEFAULT",
            'stock' => '5',
            'low_stock_limit' => '1',
            'main_image' => UploadedFile::fake()->image("queued-{$number}.jpg", 800, 1000)->size(100),
            'gallery' => [],
        ];
    }
}
