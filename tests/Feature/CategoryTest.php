<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\VideoLearningWord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token = 'IWGkI4GkjRt6fScJL1oBCCe7MYINeUGAjRiDnVMZmujUOtUbVx';

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['email' => 'admin@example.com', 'name' => 'Admin']);
    }

    private function makeVideo(int $categoryId, bool $visible = true): VideoLearningWord
    {
        return VideoLearningWord::create([
            'category_id'    => $categoryId,
            'title'          => 'Test',
            'video_path'     => 'uploads/video_learning/videos/x.mp4',
            'video_name'     => 'x.mp4',
            'thumbnail_path' => 'uploads/video_learning/thumbnails/x.jpg',
            'json_data'      => '{"word":"Apple"}',
            'is_visible'     => $visible,
        ]);
    }

    public function test_general_category_exists_after_migration()
    {
        $this->assertDatabaseHas('categories', ['name' => 'General']);
    }

    public function test_admin_can_view_category_index()
    {
        $response = $this->actingAs($this->user)->get(route('categories.index'));
        $response->assertStatus(200)->assertSee('Categories');
    }

    public function test_category_index_ajax_returns_table_partial_not_error()
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('categories.index', ['search' => 'General']));

        $response->assertStatus(200);
        $response->assertSee('General');
        // Must be the plain table partial, not the full page or an error dump.
        $response->assertDontSee('<aside class="sidebar"', false);
        $response->assertDontSee('TypeError', false);
    }

    public function test_admin_can_view_category_create_form()
    {
        $response = $this->actingAs($this->user)->get(route('categories.create'));
        $response->assertStatus(200)->assertSee('Category Name');
    }

    public function test_admin_can_store_category()
    {
        $response = $this->actingAs($this->user)->post(route('categories.store'), [
            'name' => 'Animals',
            'is_active' => '1',
        ]);
        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Animals', 'is_active' => true]);
    }

    public function test_admin_can_toggle_category_status()
    {
        $cat = Category::create(['name' => 'Fruits', 'is_active' => true, 'order_index' => 1]);
        $response = $this->actingAs($this->user)->patch(route('categories.toggle-status', $cat->id));
        $response->assertOk()->assertJson(['success' => true, 'is_active' => false]);
    }

    public function test_general_category_cannot_be_deleted()
    {
        $general = Category::where('name', 'General')->first();
        $response = $this->actingAs($this->user)->delete(route('categories.destroy', $general->id));
        $this->assertDatabaseHas('categories', ['id' => $general->id]);
    }

    public function test_deleting_category_also_deletes_its_videos()
    {
        $cat = Category::create(['name' => 'Temp', 'is_active' => true, 'order_index' => 2]);
        $video1 = $this->makeVideo($cat->id);
        $video2 = $this->makeVideo($cat->id);

        // A video in another category must NOT be touched.
        $general = Category::where('name', 'General')->first();
        $safe = $this->makeVideo($general->id);

        $this->actingAs($this->user)->delete(route('categories.destroy', $cat->id));

        $this->assertDatabaseMissing('categories', ['id' => $cat->id]);
        $this->assertDatabaseMissing('video_learning_words', ['id' => $video1->id]);
        $this->assertDatabaseMissing('video_learning_words', ['id' => $video2->id]);
        $this->assertDatabaseHas('video_learning_words', ['id' => $safe->id]);
    }

    public function test_video_create_form_shows_category_dropdown()
    {
        $response = $this->actingAs($this->user)->get(route('video-learning.create'));
        $response->assertStatus(200)
            ->assertSee('name="category_id"', false)
            ->assertSee('General');
    }

    public function test_categories_api_returns_preview_of_five_videos()
    {
        $general = Category::where('name', 'General')->first();
        for ($i = 0; $i < 8; $i++) {
            $this->makeVideo($general->id);
        }

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/categories');

        $response->assertOk()->assertJson(['status' => true]);
        $data = $response->json('data');
        $generalNode = collect($data)->firstWhere('id', $general->id);
        $this->assertEquals(8, $generalNode['total_videos']);
        $this->assertCount(5, $generalNode['videos']); // preview limited to 5
    }

    public function test_category_show_api_returns_all_videos_of_category()
    {
        $general = Category::where('name', 'General')->first();
        for ($i = 0; $i < 7; $i++) {
            $this->makeVideo($general->id);
        }

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/categories/' . $general->id);

        $response->assertOk();
        $this->assertEquals(7, $response->json('total'));
        $this->assertCount(7, $response->json('data'));
    }

    public function test_category_show_api_with_zero_returns_all_videos()
    {
        $general = Category::where('name', 'General')->first();
        $other = Category::create(['name' => 'Other', 'is_active' => true, 'order_index' => 3]);
        for ($i = 0; $i < 4; $i++) {
            $this->makeVideo($general->id);
        }
        for ($i = 0; $i < 3; $i++) {
            $this->makeVideo($other->id);
        }

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/categories/0');

        $response->assertOk();
        $this->assertNull($response->json('category'));
        $this->assertEquals(7, $response->json('total')); // all videos across all categories
    }

    public function test_categories_api_hides_inactive_category()
    {
        $inactive = Category::create(['name' => 'Hidden', 'is_active' => false, 'order_index' => 4]);
        $this->makeVideo($inactive->id);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/categories');

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertNotContains($inactive->id, $ids);
    }

    public function test_category_image_stored_with_original_name_in_correct_path()
    {
        $image = UploadedFile::fake()->image('Animal Cover.png', 200, 200);

        $this->actingAs($this->user)->post(route('categories.store'), [
            'name'      => 'Animals',
            'image'     => $image,
            'is_active' => '1',
        ]);

        $cat = Category::where('name', 'Animals')->first();

        // Stored under uploads/video_learning_with_word/<category>/category, keeping the original name.
        $this->assertEquals('uploads/video_learning_with_word/animals/category/animal-cover.png', $cat->image_path);
        $this->assertTrue(File::exists(public_path($cat->image_path)));
        $this->assertStringContainsString('uploads/video_learning_with_word/animals/category/', $cat->image_url);

        // Cleanup
        $path = $cat->image_path;
        $cat->delete();
        $this->assertFalse(File::exists(public_path($path)));
    }

    public function test_video_and_thumbnail_stored_under_category_folder()
    {
        $cat = Category::create(['name' => 'My Animals', 'is_active' => true, 'order_index' => 1]);

        $video = UploadedFile::fake()->create('lesson.mp4', 300, 'video/mp4');
        $thumb = UploadedFile::fake()->image('cover.jpg', 200, 200);

        $this->actingAs($this->user)->post(route('video-learning.store'), [
            'category_id' => $cat->id,
            'title'       => 'Lesson 1',
            'video'       => $video,
            'thumbnail'   => $thumb,
            'json_data'   => '{"word":"cat"}',
            'is_visible'  => '1',
        ]);

        $item = VideoLearningWord::where('category_id', $cat->id)->first();
        $this->assertNotNull($item);
        $this->assertStringStartsWith('uploads/video_learning_with_word/my-animals/videos/', $item->video_path);
        $this->assertStringStartsWith('uploads/video_learning_with_word/my-animals/thumbnails/', $item->thumbnail_path);
        $this->assertTrue(File::exists(public_path($item->video_path)));
        $this->assertTrue(File::exists(public_path($item->thumbnail_path)));

        // Cleanup (model hook removes files)
        $item->delete();
    }

    public function test_duplicate_video_thumbnail_names_get_timestamp_suffix()
    {
        $cat = Category::create(['name' => 'Dup Cat', 'is_active' => true, 'order_index' => 1]);

        $payload = fn() => [
            'category_id' => $cat->id,
            'title'       => 'Lesson',
            'video'       => UploadedFile::fake()->create('lesson.mp4', 100, 'video/mp4'),
            'thumbnail'   => UploadedFile::fake()->image('cover.jpg', 120, 120),
            'json_data'   => '{"word":"x"}',
            'is_visible'  => '1',
        ];

        // First upload keeps the original name.
        $this->actingAs($this->user)->post(route('video-learning.store'), $payload());
        $first = VideoLearningWord::where('category_id', $cat->id)->latest('id')->first();
        $this->assertEquals('uploads/video_learning_with_word/dup-cat/videos/lesson.mp4', $first->video_path);
        $this->assertEquals('uploads/video_learning_with_word/dup-cat/thumbnails/cover.jpg', $first->thumbnail_path);

        // Second upload with the SAME names in the SAME category gets a timestamp suffix.
        $this->actingAs($this->user)->post(route('video-learning.store'), $payload());
        $second = VideoLearningWord::where('category_id', $cat->id)->latest('id')->first();

        $this->assertNotEquals($first->video_path, $second->video_path);
        $this->assertNotEquals($first->thumbnail_path, $second->thumbnail_path);
        $this->assertMatchesRegularExpression('#/videos/lesson_\d+\.mp4$#', $second->video_path);
        $this->assertMatchesRegularExpression('#/thumbnails/cover_\d+\.jpg$#', $second->thumbnail_path);

        // Both files actually exist on disk.
        $this->assertTrue(File::exists(public_path($first->video_path)));
        $this->assertTrue(File::exists(public_path($second->video_path)));

        VideoLearningWord::where('category_id', $cat->id)->get()->each->delete();
    }

    public function test_deleting_last_video_removes_empty_category_folders()
    {
        $cat = Category::create(['name' => 'Empty Test', 'is_active' => true, 'order_index' => 1]);

        $this->actingAs($this->user)->post(route('video-learning.store'), [
            'category_id' => $cat->id,
            'title'       => 'Only One',
            'video'       => UploadedFile::fake()->create('solo.mp4', 100, 'video/mp4'),
            'thumbnail'   => UploadedFile::fake()->image('solo.jpg', 100, 100),
            'json_data'   => '{"word":"x"}',
            'is_visible'  => '1',
        ]);

        $item = VideoLearningWord::where('category_id', $cat->id)->first();
        $this->assertTrue(File::isDirectory(public_path('uploads/video_learning_with_word/empty-test/videos')));

        // Delete the only video -> its (now empty) folders should be removed.
        $this->actingAs($this->user)->delete(route('video-learning.destroy', $item->id));

        $this->assertFalse(File::isDirectory(public_path('uploads/video_learning_with_word/empty-test/videos')));
        $this->assertFalse(File::isDirectory(public_path('uploads/video_learning_with_word/empty-test/thumbnails')));
    }

    public function test_deleting_category_removes_whole_category_folder()
    {
        $cat = Category::create(['name' => 'Folder Gone', 'is_active' => true, 'order_index' => 1]);

        // Category image + one video.
        $this->actingAs($this->user)->post(route('categories.store'), [
            'name' => 'Folder Gone 2', 'is_active' => '1',
            'image' => UploadedFile::fake()->image('ic.png', 60, 60),
        ]);
        $cat2 = Category::where('name', 'Folder Gone 2')->first();
        $this->actingAs($this->user)->post(route('video-learning.store'), [
            'category_id' => $cat2->id,
            'video'       => UploadedFile::fake()->create('v.mp4', 100, 'video/mp4'),
            'json_data'   => '{"word":"x"}',
            'is_visible'  => '1',
        ]);
        $base = public_path('uploads/video_learning_with_word/folder-gone-2');
        $this->assertTrue(File::isDirectory($base));

        $this->actingAs($this->user)->delete(route('categories.destroy', $cat2->id));

        $this->assertFalse(File::isDirectory($base));
        $this->assertDatabaseMissing('categories', ['id' => $cat2->id]);
    }

    public function test_editing_category_relocates_files_to_new_folder()
    {
        $catA = Category::create(['name' => 'Cat A', 'is_active' => true, 'order_index' => 1]);
        $catB = Category::create(['name' => 'Cat B', 'is_active' => true, 'order_index' => 2]);

        $this->actingAs($this->user)->post(route('video-learning.store'), [
            'category_id' => $catA->id,
            'title'       => 'Movable',
            'video'       => UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'),
            'thumbnail'   => UploadedFile::fake()->image('clip.jpg', 100, 100),
            'json_data'   => '{"word":"x"}',
            'is_visible'  => '1',
        ]);
        $item = VideoLearningWord::where('category_id', $catA->id)->first();
        $this->assertStringContainsString('/cat-a/videos/', $item->video_path);

        // Edit: move it to Cat B (no new files uploaded, just category change).
        $this->actingAs($this->user)->post(route('video-learning.update', $item->id), [
            'category_id' => $catB->id,
            'json_data'   => '{"word":"x"}',
            'is_visible'  => '1',
        ]);

        $item->refresh();
        $this->assertEquals($catB->id, $item->category_id);
        $this->assertStringContainsString('/cat-b/videos/', $item->video_path);
        $this->assertStringContainsString('/cat-b/thumbnails/', $item->thumbnail_path);
        $this->assertTrue(File::exists(public_path($item->video_path)));
        $this->assertTrue(File::exists(public_path($item->thumbnail_path)));
        // Old Cat A folders are gone (were emptied by the move).
        $this->assertFalse(File::isDirectory(public_path('uploads/video_learning_with_word/cat-a/videos')));

        $item->delete();
    }

    public function test_newly_created_category_appears_first()
    {
        // General has order_index 0; a new one should sort before it.
        $this->actingAs($this->user)->post(route('categories.store'), ['name' => 'Newest', 'is_active' => '1']);

        $first = Category::orderBy('order_index', 'asc')->orderBy('id', 'desc')->first();
        $this->assertEquals('Newest', $first->name);
    }

    public function test_admin_can_reorder_categories()
    {
        $a = Category::create(['name' => 'Alpha', 'is_active' => true, 'order_index' => 5]);
        $b = Category::create(['name' => 'Beta', 'is_active' => true, 'order_index' => 6]);
        $c = Category::create(['name' => 'Gamma', 'is_active' => true, 'order_index' => 7]);

        // New desired order: Gamma, Alpha, Beta
        $response = $this->actingAs($this->user)->postJson(route('categories.reorder'), [
            'ordered_ids' => [$c->id, $a->id, $b->id],
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(0, $c->fresh()->order_index);
        $this->assertEquals(1, $a->fresh()->order_index);
        $this->assertEquals(2, $b->fresh()->order_index);
    }

    public function test_category_api_reflects_saved_sequence()
    {
        $general = Category::where('name', 'General')->first();
        $a = Category::create(['name' => 'Alpha', 'is_active' => true, 'order_index' => 1]);
        $b = Category::create(['name' => 'Beta', 'is_active' => true, 'order_index' => 2]);

        // Save a specific sequence: Beta, General, Alpha
        $this->actingAs($this->user)->postJson(route('categories.reorder'), [
            'ordered_ids' => [$b->id, $general->id, $a->id],
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)->getJson('/api/categories');
        $names = collect($response->json('data'))->pluck('name')->all();

        $this->assertEquals(['Beta', 'General', 'Alpha'], $names);
    }

    public function test_video_list_ajax_returns_only_table_fragment()
    {
        $cat = Category::create(['name' => 'FragCat', 'is_active' => true, 'order_index' => 1]);
        $v = $this->makeVideo($cat->id);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('video-learning.list-ajax'));

        $response->assertStatus(200)
            ->assertSee('row-' . $v->id, false)          // table rows present
            ->assertDontSee('<aside class="sidebar"', false) // NOT the full page
            ->assertDontSee('id="videoReorderModal"', false); // NOT the modals/scripts
    }

    public function test_video_index_category_filter_shows_only_that_category()
    {
        $catA = Category::create(['name' => 'FilterA', 'is_active' => true, 'order_index' => 1]);
        $catB = Category::create(['name' => 'FilterB', 'is_active' => true, 'order_index' => 2]);
        $a = $this->makeVideo($catA->id);
        $b = $this->makeVideo($catB->id);

        // AJAX list filtered to catA should include A's row and exclude B's.
        $response = $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('video-learning.list-ajax', ['category_id' => $catA->id]));

        $response->assertStatus(200)
            ->assertSee('row-' . $a->id, false)
            ->assertDontSee('row-' . $b->id, false);

        // The filter dropdown is rendered on the main page.
        $page = $this->actingAs($this->user)->get(route('video-learning.index'));
        $page->assertStatus(200)
            ->assertSee('name="category_id"', false)
            ->assertSee('All Categories');
    }

    public function test_video_index_shows_category_column_and_name()
    {
        $cat = Category::create(['name' => 'Fruits', 'is_active' => true, 'order_index' => 1]);
        $this->makeVideo($cat->id);

        $response = $this->actingAs($this->user)->get(route('video-learning.index'));
        $response->assertStatus(200)
            ->assertSee('Category', false)   // column header
            ->assertSee('Fruits')            // category name in a row
            ->assertSee('Set Index');        // the index button
    }

    public function test_category_videos_endpoint_returns_ordered_videos()
    {
        $cat = Category::create(['name' => 'Seq', 'is_active' => true, 'order_index' => 1]);
        $a = $this->makeVideo($cat->id);
        $b = $this->makeVideo($cat->id);
        $other = Category::where('name', 'General')->first();
        $this->makeVideo($other->id); // must not appear

        $response = $this->actingAs($this->user)
            ->getJson(route('video-learning.category-videos', $cat->id));

        $response->assertOk()->assertJson(['success' => true]);
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $ids);
    }

    public function test_reordering_category_videos_reflects_in_category_api()
    {
        $cat = Category::create(['name' => 'Ord', 'is_active' => true, 'order_index' => 1]);
        $a = $this->makeVideo($cat->id);
        $b = $this->makeVideo($cat->id);
        $c = $this->makeVideo($cat->id);

        // Save sequence c, a, b for this category (same endpoint the popup uses).
        $this->actingAs($this->user)->postJson(route('video-learning.reorder'), [
            'ordered_ids' => [$c->id, $a->id, $b->id],
        ])->assertOk();

        // Category API should return them in that exact saved order.
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/categories/' . $cat->id);

        $apiIds = collect($response->json('data'))->pluck('id')->all();
        $this->assertEquals([$c->id, $a->id, $b->id], $apiIds);
    }

    public function test_episode_no_is_saved_and_optional()
    {
        $cat = Category::where('name', 'General')->first();

        // With an episode number
        $this->actingAs($this->user)->post(route('video-learning.store'), [
            'category_id' => $cat->id,
            'episode_no'  => '7',
            'video'       => UploadedFile::fake()->create('ep.mp4', 80, 'video/mp4'),
            'json_data'   => '{"word":"x"}',
            'is_visible'  => '1',
        ]);
        $withEp = VideoLearningWord::latest('id')->first();
        $this->assertSame(7, $withEp->episode_no);

        // Without one -> stays null (optional)
        $this->actingAs($this->user)->post(route('video-learning.store'), [
            'category_id' => $cat->id,
            'video'       => UploadedFile::fake()->create('noep.mp4', 80, 'video/mp4'),
            'json_data'   => '{"word":"y"}',
            'is_visible'  => '1',
        ]);
        $noEp = VideoLearningWord::latest('id')->first();
        $this->assertNull($noEp->episode_no);

        $withEp->delete();
        $noEp->delete();
    }

    public function test_episode_no_is_not_exposed_in_apis()
    {
        $cat = Category::where('name', 'General')->first();
        VideoLearningWord::create([
            'category_id' => $cat->id, 'title' => 'E', 'episode_no' => 3,
            'video_path' => 'uploads/x.mp4', 'video_name' => 'x.mp4',
            'thumbnail_path' => 'uploads/x.jpg', 'json_data' => '{}', 'is_visible' => true,
        ]);

        $flat = $this->withHeader('Authorization', 'Bearer ' . $this->token)->getJson('/api/video-learning-words');
        $this->assertArrayNotHasKey('episode_no', $flat->json('data.0'));

        $byCat = $this->withHeader('Authorization', 'Bearer ' . $this->token)->getJson('/api/categories/' . $cat->id);
        $this->assertArrayNotHasKey('episode_no', $byCat->json('data.0'));

        $all = $this->withHeader('Authorization', 'Bearer ' . $this->token)->getJson('/api/categories');
        $firstVideo = $all->json('data.0.videos.0');
        if ($firstVideo) {
            $this->assertArrayNotHasKey('episode_no', $firstVideo);
        }
    }

    public function test_episode_no_included_in_reorder_popup_data()
    {
        $cat = Category::create(['name' => 'Ep Pop', 'is_active' => true, 'order_index' => 1]);
        VideoLearningWord::create([
            'category_id' => $cat->id, 'title' => 'E', 'episode_no' => 12,
            'video_path' => 'uploads/x.mp4', 'video_name' => 'x.mp4',
            'thumbnail_path' => 'uploads/x.jpg', 'json_data' => '{}', 'is_visible' => true,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('video-learning.category-videos', $cat->id));
        $response->assertOk();
        $this->assertSame(12, $response->json('data.0.episode_no'));
    }

    public function test_category_api_optional_pagination_and_backward_compatible()
    {
        $general = Category::where('name', 'General')->first();
        for ($i = 0; $i < 6; $i++) {
            $this->makeVideo($general->id);
        }

        // Default (no per_page) -> full list, NO pagination key (unchanged behavior).
        $full = $this->withHeader('Authorization', 'Bearer ' . $this->token)->getJson('/api/categories/0');
        $this->assertArrayNotHasKey('pagination', $full->json());
        $this->assertEquals(6, $full->json('total'));
        $this->assertCount(6, $full->json('data'));

        // With per_page -> paginated + meta.
        $paged = $this->withHeader('Authorization', 'Bearer ' . $this->token)->getJson('/api/categories/0?per_page=2&page=1');
        $this->assertCount(2, $paged->json('data'));
        $this->assertEquals(6, $paged->json('pagination.total'));
        $this->assertEquals(2, $paged->json('pagination.per_page'));
        $this->assertEquals(3, $paged->json('pagination.last_page'));
    }

    public function test_flat_video_api_optional_pagination_backward_compatible()
    {
        $general = Category::where('name', 'General')->first();
        for ($i = 0; $i < 5; $i++) {
            $this->makeVideo($general->id);
        }

        // Default: unchanged shape, no pagination key.
        $full = $this->withHeader('Authorization', 'Bearer ' . $this->token)->getJson('/api/video-learning-words');
        $this->assertArrayNotHasKey('pagination', $full->json());
        $this->assertEquals(5, $full->json('total'));

        // Opt-in pagination.
        $paged = $this->withHeader('Authorization', 'Bearer ' . $this->token)->getJson('/api/video-learning-words?per_page=2');
        $this->assertCount(2, $paged->json('data'));
        $this->assertEquals(5, $paged->json('pagination.total'));
    }

    public function test_category_videos_popup_endpoint_caps_and_excludes_json()
    {
        $cat = Category::create(['name' => 'Big', 'is_active' => true, 'order_index' => 1]);
        $this->makeVideo($cat->id);

        $response = $this->actingAs($this->user)->getJson(route('video-learning.category-videos', $cat->id));
        $response->assertOk();
        $this->assertEquals(500, $response->json('limit'));
        $this->assertFalse($response->json('limited'));
        $this->assertArrayNotHasKey('json_data', $response->json('data.0'));
    }

    public function test_post_category_videos_by_body_id()
    {
        $general = Category::where('name', 'General')->first();
        $a = $this->makeVideo($general->id);
        $b = $this->makeVideo($general->id);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/categories/videos', ['category_id' => $general->id]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertEquals('General', $response->json('category.name'));
        $this->assertEquals(2, $response->json('total'));
    }

    public function test_post_category_videos_zero_returns_all()
    {
        $general = Category::where('name', 'General')->first();
        $other = Category::create(['name' => 'Oth', 'is_active' => true, 'order_index' => 1]);
        $this->makeVideo($general->id);
        $this->makeVideo($other->id);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/categories/videos', ['category_id' => 0]);

        $response->assertOk();
        $this->assertNull($response->json('category'));
        $this->assertEquals(2, $response->json('total'));
    }

    public function test_post_category_videos_requires_category_id()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/categories/videos', []);

        $response->assertStatus(422)->assertJson(['status' => false]);
    }

    public function test_existing_video_api_still_works_unchanged()
    {
        $general = Category::where('name', 'General')->first();
        $this->makeVideo($general->id);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/video-learning-words');

        $response->assertOk()->assertJson(['status' => true]);
        $first = $response->json('data.0');
        $this->assertEqualsCanonicalizing(
            ['id', 'video_url', 'thumbnail_url', 'json_data', 'created_at', 'updated_at'],
            array_keys($first)
        );
    }
}
