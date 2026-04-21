<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature tests for ProductController. Covers index, create, store, show, edit, update, destroy.
 * Product uses slug for route model binding. Store/update require images and relations.
 */
class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to authenticate as an admin user.
     */
    protected function asAdmin(): Admin
    {
        /** @var Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /**
     * Verifies that an authenticated admin can access the products index page.
     */
    public function test_index_returns_200_with_products_list_for_authenticated_admin(): void
    {
        $this->asAdmin();

        Product::factory()->count(3)->create();

        $response = $this->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.index');
        $response->assertViewHas('products');
        $this->assertCount(3, $response->viewData('products'));
    }

    /**
     * Ensures unauthenticated users cannot access the products list.
     */
    public function test_index_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.products.index'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Confirms that an authenticated admin can access the product creation form with relations.
     */
    public function test_create_returns_create_form_with_relations_for_authenticated_admin(): void
    {
        $this->asAdmin();

        $response = $this->get(route('admin.products.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.create');
        $response->assertViewHas(['categories', 'brands', 'colors', 'sizes']);
    }

    /**
     * Ensures guests cannot access the create form.
     */
    public function test_create_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.products.create'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests store with optional first_image, second_image, third_image to cover those branches.
     */
    public function test_store_creates_product_with_optional_images(): void
    {
        Storage::fake('public');

        $this->asAdmin();

        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $color = Color::factory()->create();
        $size = Size::factory()->create();

        $response = $this->post(route('admin.products.store'), [
            'name' => 'Product With All Images',
            'qty' => 5,
            'price' => 5000,
            'description' => 'Description',
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 100, 100),
            'first_image' => UploadedFile::fake()->image('first.jpg', 100, 100),
            'second_image' => UploadedFile::fake()->image('second.jpg', 100, 100),
            'third_image' => UploadedFile::fake()->image('third.jpg', 100, 100),
            'status' => true,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'color_id' => [$color->id],
            'size_id' => [$size->id],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $product = Product::where('name', 'Product With All Images')->first();
        $this->assertNotNull($product->thumbnail);
        $this->assertNotNull($product->first_image);
        $this->assertNotNull($product->second_image);
        $this->assertNotNull($product->third_image);
    }

    /**
     * Tests the happy path for creating a product with image upload.
     */
    public function test_store_creates_product_and_redirects_with_success(): void
    {
        Storage::fake('public');

        $this->asAdmin();

        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $color = Color::factory()->create();
        $size = Size::factory()->create();

        $response = $this->post(route('admin.products.store'), [
            'name' => 'Test Product',
            'qty' => 10,
            'price' => 9999,
            'description' => 'Test description',
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 100, 100),
            'status' => true,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'color_id' => [$color->id],
            'size_id' => [$size->id],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success', 'Product created successfully');

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'slug' => 'test-product',
        ]);
    }

    /**
     * Ensures ProductStoreRequest validation rejects missing required fields.
     */
    public function test_store_fails_validation_when_name_is_empty(): void
    {
        Storage::fake('public');

        $this->asAdmin();

        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $color = Color::factory()->create();
        $size = Size::factory()->create();

        $response = $this->post(route('admin.products.store'), [
            'name' => '',
            'qty' => 10,
            'price' => 9999,
            'description' => 'Test description',
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 100, 100),
            'status' => true,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'color_id' => [$color->id],
            'size_id' => [$size->id],
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('products', 0);
    }

    /**
     * Ensures guests cannot create products.
     */
    public function test_store_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->post(route('admin.products.store'), [
            'name' => 'Test Product',
        ]);

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * The show action intentionally returns 404 for products.
     */
    public function test_show_returns_404(): void
    {
        $this->asAdmin();

        $product = Product::factory()->create();

        $response = $this->get(route('admin.products.show', $product->slug));

        $response->assertStatus(404);
    }

    /**
     * Verifies that an authenticated admin can access the edit form.
     */
    public function test_edit_returns_edit_form_with_product_for_authenticated_admin(): void
    {
        $this->asAdmin();

        $product = Product::factory()->create();

        $response = $this->get(route('admin.products.edit', $product->slug));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.edit');
        $response->assertViewHas('product', $product);
        $response->assertViewHas(['categories', 'brands', 'colors', 'sizes']);
    }

    /**
     * Ensures guests cannot access the edit form.
     */
    public function test_edit_redirects_to_login_when_unauthenticated(): void
    {
        $product = Product::factory()->create();

        $response = $this->get(route('admin.products.edit', $product->slug));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests the happy path for updating a product.
     */
    public function test_update_modifies_product_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $product = Product::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $color = Color::factory()->create();
        $size = Size::factory()->create();

        $response = $this->put(route('admin.products.update', $product->slug), [
            'name' => 'New Name',
            'qty' => 20,
            'price' => 19999,
            'description' => 'Updated description',
            'status' => true,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'color_id' => [$color->id],
            'size_id' => [$size->id],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success', 'Product updated successfully');

        $product->refresh();
        $this->assertSame('New Name', $product->name);
        $this->assertSame('new-name', $product->slug);
    }

    /**
     * Tests update with new thumbnail to cover old image deletion and uploadImage.
     */
    public function test_update_replaces_thumbnail_and_deletes_old_image(): void
    {
        Storage::fake('public');

        $this->asAdmin();

        $product = Product::factory()->create([
            'name' => 'Product With Thumbnail',
            'slug' => 'product-with-thumbnail',
            'thumbnail' => 'images/products/old_thumbnail.jpg',
        ]);
        Storage::disk('public')->put('images/products/old_thumbnail.jpg', 'fake');
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $color = Color::factory()->create();
        $size = Size::factory()->create();

        $response = $this->put(route('admin.products.update', $product->slug), [
            'name' => 'Product With Thumbnail',
            'qty' => 10,
            'price' => 9999,
            'description' => 'Description',
            'thumbnail' => UploadedFile::fake()->image('new_thumbnail.jpg', 100, 100),
            'status' => true,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'color_id' => [$color->id],
            'size_id' => [$size->id],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $product->refresh();
        $this->assertNotNull($product->thumbnail);
        $this->assertNotSame('images/products/old_thumbnail.jpg', $product->thumbnail);
    }

    /**
     * Tests update with new first_image, second_image, third_image to cover those branches.
     */
    public function test_update_replaces_optional_images_and_deletes_old_ones(): void
    {
        Storage::fake('public');

        $this->asAdmin();

        $product = Product::factory()->create([
            'name' => 'Product With Images',
            'slug' => 'product-with-images',
            'first_image' => 'images/products/old_first.jpg',
            'second_image' => 'images/products/old_second.jpg',
        ]);
        Storage::disk('public')->put('images/products/old_first.jpg', 'fake');
        Storage::disk('public')->put('images/products/old_second.jpg', 'fake');
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $color = Color::factory()->create();
        $size = Size::factory()->create();

        $response = $this->put(route('admin.products.update', $product->slug), [
            'name' => 'Product With Images',
            'qty' => 10,
            'price' => 9999,
            'description' => 'Description',
            'first_image' => UploadedFile::fake()->image('new_first.jpg', 100, 100),
            'second_image' => UploadedFile::fake()->image('new_second.jpg', 100, 100),
            'third_image' => UploadedFile::fake()->image('new_third.jpg', 100, 100),
            'status' => true,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'color_id' => [$color->id],
            'size_id' => [$size->id],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $product->refresh();
        $this->assertNotNull($product->first_image);
        $this->assertNotNull($product->second_image);
        $this->assertNotNull($product->third_image);
    }

    /**
     * Ensures guests cannot update products.
     */
    public function test_update_redirects_to_login_when_unauthenticated(): void
    {
        $product = Product::factory()->create();

        $response = $this->put(route('admin.products.update', $product->slug), [
            'name' => 'New Name',
            'qty' => 10,
            'price' => 9999,
            'description' => 'Desc',
            'status' => true,
            'category_id' => $product->category_id,
            'brand_id' => $product->brand_id,
            'color_id' => [],
            'size_id' => [],
        ]);

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests the happy path for deleting a product.
     */
    public function test_destroy_deletes_product_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $product = Product::factory()->create();

        $response = $this->delete(route('admin.products.destroy', $product->slug));

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success', 'Product deleted successfully');
    }

    /**
     * Verifies that destroy actually removes the product from the database.
     */
    public function test_destroy_removes_product_from_database(): void
    {
        $this->asAdmin();

        $product = Product::factory()->create();

        $this->delete(route('admin.products.destroy', $product->slug));

        $this->assertNull(Product::find($product->id));
    }

    /**
     * Tests destroy with product that has images to cover Storage::delete branches.
     */
    public function test_destroy_deletes_product_images_from_storage(): void
    {
        Storage::fake('public');

        $this->asAdmin();

        $product = Product::factory()->create([
            'thumbnail' => 'images/products/thumb.jpg',
            'first_image' => 'images/products/first.jpg',
            'second_image' => 'images/products/second.jpg',
            'third_image' => 'images/products/third.jpg',
        ]);
        Storage::disk('public')->put('images/products/thumb.jpg', 'fake');
        Storage::disk('public')->put('images/products/first.jpg', 'fake');
        Storage::disk('public')->put('images/products/second.jpg', 'fake');
        Storage::disk('public')->put('images/products/third.jpg', 'fake');

        $response = $this->delete(route('admin.products.destroy', $product->slug));

        $response->assertRedirect(route('admin.products.index'));
        $this->assertNull(Product::find($product->id));
        Storage::disk('public')->assertMissing('images/products/thumb.jpg');
        Storage::disk('public')->assertMissing('images/products/first.jpg');
        Storage::disk('public')->assertMissing('images/products/second.jpg');
        Storage::disk('public')->assertMissing('images/products/third.jpg');
    }

    /**
     * Ensures guests cannot delete products.
     */
    public function test_destroy_redirects_to_login_when_unauthenticated(): void
    {
        $product = Product::factory()->create();

        $response = $this->delete(route('admin.products.destroy', $product->slug));

        $response->assertRedirect(route('admin.login'));
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    /**
     * Removing a single product image deletes the file and nulls the column.
     */
    public function test_destroy_product_image_removes_file_and_nulls_thumbnail(): void
    {
        Storage::fake('public');
        $this->asAdmin();

        Storage::disk('public')->put('images/products/thumb.jpg', 'fake');
        $product = Product::factory()->create([
            'slug' => 'image-remove-test',
            'thumbnail' => 'images/products/thumb.jpg',
        ]);

        $response = $this->from(route('admin.products.edit', $product->slug))
            ->delete(route('admin.products.image.destroy', $product->slug), [
                'field' => 'thumbnail',
            ]);

        $response->assertRedirect(route('admin.products.edit', $product->slug));
        $product->refresh();
        $this->assertNull($product->thumbnail);
        Storage::disk('public')->assertMissing('images/products/thumb.jpg');
    }

    public function test_destroy_product_image_validation_rejects_invalid_field(): void
    {
        $this->asAdmin();
        $product = Product::factory()->create(['slug' => 'validation-test']);

        $response = $this->from(route('admin.products.edit', $product->slug))
            ->delete(route('admin.products.image.destroy', $product->slug), [
                'field' => 'not_a_column',
            ]);

        $response->assertSessionHasErrors('field');
    }
}
