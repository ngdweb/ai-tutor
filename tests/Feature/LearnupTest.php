<?php

namespace Tests\Feature;

use App\Models\LearnupCategory;
use App\Models\LearnupWord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LearnupTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token = 'IWGkI4GkjRt6fScJL1oBCCe7MYINeUGAjRiDnVMZmujUOtUbVx';

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        LearnupWord::clearCategorySlugCache();
    }

    public function test_general_learnup_category_seeded()
    {
        $this->assertDatabaseHas('learnup_categories', ['name' => 'General']);
    }

    public function test_admin_can_view_learnup_index()
    {
        $this->actingAs($this->user)->get(route('learnup.index'))
            ->assertStatus(200)->assertSee('LearnUp with AI');
    }

    public function test_admin_can_view_learnup_categories_index()
    {
        $this->actingAs($this->user)->get(route('learnup-categories.index'))
            ->assertStatus(200)->assertSee('LearnUp Categories');
    }

    public function test_can_create_learnup_content_in_correct_folder()
    {
        $cat = LearnupCategory::create(['name' => 'AI Topics', 'is_active' => true, 'order_index' => 1]);

        $this->actingAs($this->user)->post(route('learnup.store'), [
            'category_id' => $cat->id,
            'title'       => 'Lesson 1',
            'episode_no'  => '1',
            'video'       => UploadedFile::fake()->create('intro.mp4', 120, 'video/mp4'),
            'thumbnail'   => UploadedFile::fake()->image('intro.jpg', 100, 100),
            'json_data'   => '{"word":"ai"}',
            'is_visible'  => '1',
        ])->assertRedirect(route('learnup.index'));

        $item = LearnupWord::where('category_id', $cat->id)->first();
        $this->assertNotNull($item);
        $this->assertSame(1, $item->episode_no);

        // Files stored under the LearnUp folder, not the video module folder.
        $vRel = $item->getVideoRelativePath();
        $tRel = $item->getThumbnailRelativePath();
        $this->assertStringContainsString('uploads/learnup_with_ai/ai-topics/videos/', $vRel);
        $this->assertStringContainsString('uploads/learnup_with_ai/ai-topics/thumbnails/', $tRel);
        $this->assertTrue(File::exists(public_path($vRel)));
        $this->assertTrue(File::exists(public_path($tRel)));

        $item->delete();
        // tidy the created folders
        File::deleteDirectory(public_path('uploads/learnup_with_ai/ai-topics'));
    }

    public function test_learnup_api_returns_category_with_videos()
    {
        $cat = LearnupCategory::create(['name' => 'ApiCat', 'is_active' => true, 'order_index' => 1]);
        LearnupWord::create([
            'category_id' => $cat->id, 'title' => 'X', 'episode_no' => 2,
            'video_path' => 'v.mp4', 'video_name' => 'v.mp4',
            'thumbnail_path' => 't.jpg', 'json_data' => '{"a":1}', 'is_visible' => true,
        ]);

        // list endpoint: category with videos appears; episode_no not exposed
        $list = $this->withHeader('Authorization', 'Bearer ' . $this->token)->getJson('/api/learnup-categories');
        $names = collect($list->json('data'))->pluck('name')->all();
        $this->assertContains('ApiCat', $names);
        $firstVideo = collect($list->json('data'))->firstWhere('name', 'ApiCat')['videos'][0];
        $this->assertArrayNotHasKey('episode_no', $firstVideo);

        // flat endpoint
        $flat = $this->withHeader('Authorization', 'Bearer ' . $this->token)->getJson('/api/learnup-words');
        $this->assertEquals(1, $flat->json('total'));

        // POST by body, 0 = all
        $all = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/learnup-categories/videos', ['category_id' => 0]);
        $this->assertEquals(1, $all->json('total'));
    }

    public function test_learnup_does_not_affect_video_module()
    {
        // Creating LearnUp data leaves the video_learning_words table untouched.
        $cat = LearnupCategory::create(['name' => 'Z', 'is_active' => true, 'order_index' => 1]);
        LearnupWord::create([
            'category_id' => $cat->id, 'title' => 'X',
            'video_path' => 'v.mp4', 'thumbnail_path' => 't.jpg',
            'json_data' => '{}', 'is_visible' => true,
        ]);

        $this->assertEquals(0, \App\Models\VideoLearningWord::count());
    }
}
