<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VideoLearningWord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class VideoLearningWordTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin'
        ]);
    }

    /**
     * Test admin can view video learning module index page
     */
    public function test_admin_can_view_video_learning_index()
    {
        $response = $this->actingAs($this->user)->get(route('video-learning.index'));
        $response->assertStatus(200);
        $response->assertSee('Video Learning with Word');
    }

    /**
     * Test admin can view dedicated create screen
     */
    public function test_admin_can_view_create_screen()
    {
        $response = $this->actingAs($this->user)->get(route('video-learning.create'));
        $response->assertStatus(200);
        $response->assertSee('Add Video Learning Record(s)');
        $response->assertSee('Add More Record');
    }

    /**
     * Test admin can view dedicated edit screen
     */
    public function test_admin_can_view_edit_screen()
    {
        $fakeVideo = UploadedFile::fake()->create('sample.mp4', 500, 'video/mp4');
        $this->actingAs($this->user)->post(route('video-learning.store'), [
            'title' => 'Initial Word',
            'video' => $fakeVideo,
            'json_data' => '{"word": "Initial"}',
            'is_visible' => '1',
        ]);
        $item = VideoLearningWord::first();

        $response = $this->actingAs($this->user)->get(route('video-learning.edit', $item->id));
        $response->assertStatus(200);
        $response->assertSee('Edit Video Learning');
        $response->assertSee('Add More Record');

        $item->delete();
    }

    /**
     * Test can create multiple records in one batch submission
     */
    public function test_can_batch_create_multiple_video_learning_records()
    {
        $video1 = UploadedFile::fake()->create('batch1.mp4', 500, 'video/mp4');
        $thumb1 = UploadedFile::fake()->image('thumb1.jpg', 300, 200);
        $video2 = UploadedFile::fake()->create('batch2.mp4', 500, 'video/mp4');

        $payload = [
            'records' => [
                [
                    'title' => 'Batch Record 1',
                    'video' => $video1,
                    'thumbnail' => $thumb1,
                    'json_data' => '{"word": "Batch1"}',
                    'is_visible' => '1',
                ],
                [
                    'title' => 'Batch Record 2',
                    'video' => $video2,
                    'json_data' => '{"word": "Batch2"}',
                    'is_visible' => '1',
                ],
            ]
        ];

        $response = $this->actingAs($this->user)->post(route('video-learning.store'), $payload);
        $response->assertRedirect(route('video-learning.index'));

        $this->assertDatabaseHas('video_learning_words', ['title' => 'Batch Record 1']);
        $this->assertDatabaseHas('video_learning_words', ['title' => 'Batch Record 2']);
        $this->assertEquals(2, VideoLearningWord::count());

        VideoLearningWord::all()->each->delete();
    }

    /**
     * Test create video learning word with custom thumbnail
     */
    public function test_can_create_video_learning_word_with_custom_thumbnail()
    {
        $fakeVideo = UploadedFile::fake()->create('sample_lesson.mp4', 1024, 'video/mp4');
        $fakeThumb = UploadedFile::fake()->image('custom_thumb.jpg', 640, 360);

        $jsonData = json_encode([
            'word' => 'Elephant',
            'meaning' => 'A large plant-eating mammal',
            'timestamps' => [0, 4.2]
        ]);

        $response = $this->actingAs($this->user)->post(route('video-learning.store'), [
            'title' => 'Vocabulary Elephant',
            'video' => $fakeVideo,
            'thumbnail' => $fakeThumb,
            'json_data' => $jsonData,
            'is_visible' => '1',
        ]);

        $response->assertRedirect(route('video-learning.index'));
        $this->assertDatabaseHas('video_learning_words', [
            'title' => 'Vocabulary Elephant',
            'is_visible' => true,
        ]);

        $item = VideoLearningWord::first();
        $this->assertNotNull($item);
        $this->assertStringContainsString('video_', $item->video_path);
        $this->assertStringContainsString('thumb_', $item->thumbnail_path);
        $this->assertTrue(File::exists(public_path($item->video_path)));
        $this->assertTrue(File::exists(public_path($item->thumbnail_path)));

        // Clean up created file
        $item->delete();
    }

    /**
     * Test create video learning word with NO thumbnail (auto-generated)
     */
    public function test_can_create_video_learning_word_with_auto_generated_thumbnail()
    {
        $fakeVideo = UploadedFile::fake()->create('another_video.mp4', 1024, 'video/mp4');
        $jsonData = json_encode(['word' => 'Tiger']);

        $response = $this->actingAs($this->user)->post(route('video-learning.store'), [
            'title' => 'Tiger Video',
            'video' => $fakeVideo,
            'json_data' => $jsonData,
            'is_visible' => '1',
        ]);

        $response->assertRedirect(route('video-learning.index'));
        $item = VideoLearningWord::first();
        $this->assertNotNull($item);
        $this->assertNotNull($item->thumbnail_path);
        $this->assertStringContainsString('thumb_', $item->thumbnail_path);
        $this->assertTrue(File::exists(public_path($item->thumbnail_path)));

        // Clean up
        $item->delete();
    }

    /**
     * Test updating record with new thumbnail deletes old thumbnail
     */
    public function test_update_record_replaces_and_deletes_old_file()
    {
        $fakeVideo1 = UploadedFile::fake()->create('lesson.mp4', 500, 'video/mp4');
        $fakeThumb1 = UploadedFile::fake()->image('lesson.jpg', 300, 200);

        $this->actingAs($this->user)->post(route('video-learning.store'), [
            'title' => 'Initial Title',
            'video' => $fakeVideo1,
            'thumbnail' => $fakeThumb1,
            'json_data' => '{"word": "Initial"}',
            'is_visible' => '1',
        ]);

        $item = VideoLearningWord::first();
        $oldVideoPath = $item->video_path;
        $oldThumbPath = $item->thumbnail_path;

        $this->assertTrue(File::exists(public_path($oldVideoPath)));
        $this->assertTrue(File::exists(public_path($oldThumbPath)));

        // Sleep 1 second so time() timestamp increments
        sleep(1);

        // Update with same filename 'lesson.mp4' and 'lesson.jpg'
        $fakeVideo2 = UploadedFile::fake()->create('lesson.mp4', 600, 'video/mp4');
        $fakeThumb2 = UploadedFile::fake()->image('lesson.jpg', 400, 300);

        $response = $this->actingAs($this->user)->post(route('video-learning.update', $item->id), [
            'title' => 'Updated Title',
            'video' => $fakeVideo2,
            'thumbnail' => $fakeThumb2,
            'json_data' => '{"word": "Updated"}',
            'is_visible' => '0',
        ]);

        $response->assertRedirect(route('video-learning.index'));
        $item->refresh();

        $this->assertEquals('Updated Title', $item->title);
        $this->assertFalse($item->is_visible);

        // Paths must be distinct because of unique timestamp + random hash
        $this->assertNotEquals($oldVideoPath, $item->video_path);
        $this->assertNotEquals($oldThumbPath, $item->thumbnail_path);

        // Old files MUST be deleted from disk
        $this->assertFalse(File::exists(public_path($oldVideoPath)));
        $this->assertFalse(File::exists(public_path($oldThumbPath)));

        // New files MUST exist on disk
        $this->assertTrue(File::exists(public_path($item->video_path)));
        $this->assertTrue(File::exists(public_path($item->thumbnail_path)));

        $item->delete();
    }

    /**
     * Test delete record removes physical files
     */
    public function test_delete_record_cleans_up_files_from_disk()
    {
        $fakeVideo = UploadedFile::fake()->create('video_to_delete.mp4', 500, 'video/mp4');
        $fakeThumb = UploadedFile::fake()->image('thumb_to_delete.jpg', 300, 200);

        $this->actingAs($this->user)->post(route('video-learning.store'), [
            'title' => 'Item to delete',
            'video' => $fakeVideo,
            'thumbnail' => $fakeThumb,
            'json_data' => '{"word": "DeleteMe"}',
            'is_visible' => '1',
        ]);

        $item = VideoLearningWord::first();
        $videoPath = $item->video_path;
        $thumbPath = $item->thumbnail_path;

        $this->assertTrue(File::exists(public_path($videoPath)));
        $this->assertTrue(File::exists(public_path($thumbPath)));

        $response = $this->actingAs($this->user)->delete(route('video-learning.destroy', $item->id));
        $response->assertRedirect(route('video-learning.index'));

        $this->assertDatabaseMissing('video_learning_words', ['id' => $item->id]);
        $this->assertFalse(File::exists(public_path($videoPath)));
        $this->assertFalse(File::exists(public_path($thumbPath)));
    }

    /**
     * Test AJAX toggle visibility (Show / Hide)
     */
    public function test_toggle_visibility_ajax()
    {
        $fakeVideo = UploadedFile::fake()->create('video_toggle.mp4', 500, 'video/mp4');
        $this->actingAs($this->user)->post(route('video-learning.store'), [
            'title' => 'Toggle Test',
            'video' => $fakeVideo,
            'json_data' => '{"word": "Toggle"}',
            'is_visible' => '1',
        ]);

        $item = VideoLearningWord::first();
        $this->assertTrue($item->is_visible);

        // Toggle to false
        $res = $this->actingAs($this->user)->patchJson(route('video-learning.toggle-visibility', $item->id));
        $res->assertStatus(200);
        $res->assertJson(['success' => true, 'is_visible' => false]);
        $item->refresh();
        $this->assertFalse($item->is_visible);

        // Toggle back to true
        $res2 = $this->actingAs($this->user)->patchJson(route('video-learning.toggle-visibility', $item->id));
        $res2->assertStatus(200);
        $res2->assertJson(['success' => true, 'is_visible' => true]);
        $item->refresh();
        $this->assertTrue($item->is_visible);

        $item->delete();
    }

    /**
     * Test GET API endpoint returns only visible items
     */
    public function test_api_endpoint_returns_only_visible_items()
    {
        $fakeVideo1 = UploadedFile::fake()->create('visible_video.mp4', 500, 'video/mp4');
        $fakeVideo2 = UploadedFile::fake()->create('hidden_video.mp4', 500, 'video/mp4');

        $this->actingAs($this->user)->post(route('video-learning.store'), [
            'title' => 'Visible Item',
            'video' => $fakeVideo1,
            'json_data' => '{"word": "Sun"}',
            'is_visible' => '1',
        ]);

        $this->actingAs($this->user)->post(route('video-learning.store'), [
            'title' => 'Hidden Item',
            'video' => $fakeVideo2,
            'json_data' => '{"word": "Moon"}',
            'is_visible' => '0',
        ]);

        $apiToken = env('API_TOKEN', 'IWGkI4GkjRt6fScJL1oBCCe7MYINeUGAjRiDnVMZmujUOtUbVx');

        // Test without token returns 401 Unauthorized
        $unauthResponse = $this->getJson('/api/video-learning-words');
        $unauthResponse->assertStatus(401);
        $unauthResponse->assertJson(['status' => false]);

        // Test with valid Bearer token
        $response = $this->withHeader('Authorization', 'Bearer ' . $apiToken)->getJson('/api/video-learning-words');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'total',
            'data' => [
                '*' => ['id', 'video_url', 'thumbnail_url', 'json_data', 'created_at', 'updated_at']
            ]
        ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertArrayNotHasKey('title', $data[0]);
        $this->assertArrayNotHasKey('video_name', $data[0]);
        $this->assertEquals('Sun', $data[0]['json_data']['word']);

        // Clean up
        VideoLearningWord::all()->each->delete();
    }

    /**
     * Test reorder endpoint updates sequence and API respects order
     */
    public function test_reorder_endpoint_updates_sequence_and_api_respects_order()
    {
        $video1 = UploadedFile::fake()->create('vid1.mp4', 500, 'video/mp4');
        $video2 = UploadedFile::fake()->create('vid2.mp4', 500, 'video/mp4');

        $this->actingAs($this->user)->post(route('video-learning.store'), [
            'title' => 'First Created',
            'video' => $video1,
            'json_data' => '{"word": "First"}',
            'is_visible' => '1',
        ]);
        $this->actingAs($this->user)->post(route('video-learning.store'), [
            'title' => 'Second Created',
            'video' => $video2,
            'json_data' => '{"word": "Second"}',
            'is_visible' => '1',
        ]);

        $item1 = VideoLearningWord::where('title', 'First Created')->first();
        $item2 = VideoLearningWord::where('title', 'Second Created')->first();

        // Drag and drop / reorder item2 to top, item1 to second
        $reorderResponse = $this->actingAs($this->user)->postJson(route('video-learning.reorder'), [
            'ordered_ids' => [$item2->id, $item1->id],
        ]);

        $reorderResponse->assertStatus(200);
        $reorderResponse->assertJson(['success' => true]);

        $item2->refresh();
        $item1->refresh();

        $this->assertEquals(0, $item2->order_index);
        $this->assertEquals(1, $item1->order_index);

        $apiToken = env('API_TOKEN', 'IWGkI4GkjRt6fScJL1oBCCe7MYINeUGAjRiDnVMZmujUOtUbVx');

        // API should return item2 first when authenticated with Bearer token
        $apiResponse = $this->withHeader('Authorization', 'Bearer ' . $apiToken)->getJson('/api/video-learning-words');
        $apiResponse->assertStatus(200);
        $apiData = $apiResponse->json('data');
        $this->assertEquals($item2->id, $apiData[0]['id']);
        $this->assertEquals($item1->id, $apiData[1]['id']);

        VideoLearningWord::all()->each->delete();
    }

    /**
     * Test admin can view API list page
     */
    public function test_admin_can_view_api_list_page()
    {
        $response = $this->actingAs($this->user)->get(route('api-list.index'));
        $response->assertStatus(200);
        $response->assertSee('API List');
        $response->assertSee('/api/video-learning-words');
        $response->assertSee('Method:');
        $response->assertSee('URL:');
        $response->assertSee('Authorization');
    }

    /**
     * Test newly created and edited records appear first in both Web index and API response
     */
    public function test_latest_added_or_edited_records_appear_first_in_list_and_api()
    {
        $video1 = UploadedFile::fake()->create('item1.mp4', 500, 'video/mp4');
        $video2 = UploadedFile::fake()->create('item2.mp4', 500, 'video/mp4');

        $this->actingAs($this->user)->post(route('video-learning.store'), [
            'title' => 'Alpha Item',
            'video' => $video1,
            'json_data' => '{"word": "Alpha"}',
            'is_visible' => '1',
        ]);
        $this->actingAs($this->user)->post(route('video-learning.store'), [
            'title' => 'Beta Item',
            'video' => $video2,
            'json_data' => '{"word": "Beta"}',
            'is_visible' => '1',
        ]);

        $item1 = VideoLearningWord::where('title', 'Alpha Item')->first();
        $item2 = VideoLearningWord::where('title', 'Beta Item')->first();

        // 1. Initially Beta Item (created last) should be first
        $indexResponse = $this->actingAs($this->user)->get(route('video-learning.index'));
        $itemsInView = $indexResponse->viewData('items');
        $this->assertEquals($item2->id, $itemsInView->first()->id);

        $apiToken = env('API_TOKEN', 'IWGkI4GkjRt6fScJL1oBCCe7MYINeUGAjRiDnVMZmujUOtUbVx');
        $apiResponse = $this->withHeader('Authorization', 'Bearer ' . $apiToken)->getJson('/api/video-learning-words');
        $this->assertEquals($item2->id, $apiResponse->json('data.0.id'));

        // 2. Now edit Alpha Item (item1). It should move to the first position!
        sleep(1);
        $this->actingAs($this->user)->post(route('video-learning.update', $item1->id), [
            'title' => 'Alpha Item Updated',
            'json_data' => '{"word": "Alpha Updated"}',
            'is_visible' => '1',
        ]);

        $indexResponseAfterUpdate = $this->actingAs($this->user)->get(route('video-learning.index'));
        $itemsInViewAfterUpdate = $indexResponseAfterUpdate->viewData('items');
        $this->assertEquals($item1->id, $itemsInViewAfterUpdate->first()->id);

        $apiResponseAfterUpdate = $this->withHeader('Authorization', 'Bearer ' . $apiToken)->getJson('/api/video-learning-words');
        $this->assertEquals($item1->id, $apiResponseAfterUpdate->json('data.0.id'));

        VideoLearningWord::all()->each->delete();
    }
}
