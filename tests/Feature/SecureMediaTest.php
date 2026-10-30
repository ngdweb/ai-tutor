<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\VideoLearningWord;
use App\Support\SecureMediaHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SecureMediaTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    // Placed in the legacy folders the model's path resolver checks, keyed by basename.
    protected string $imgName = 'sectest_pic.png';
    protected string $vidName = 'sectest_clip.mp4';
    protected string $relImage = 'uploads/video_learning/thumbnails/sectest_pic.png';
    protected string $relVideo = 'uploads/video_learning/videos/sectest_clip.mp4';

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        VideoLearningWord::clearCategorySlugCache();

        File::ensureDirectoryExists(public_path('uploads/video_learning/thumbnails'));
        File::ensureDirectoryExists(public_path('uploads/video_learning/videos'));
        // 1x1 png + tiny fake mp4
        File::put(public_path($this->relImage), base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));
        File::put(public_path($this->relVideo), 'FAKEMP4BYTES');
    }

    protected function tearDown(): void
    {
        @unlink(public_path($this->relImage));
        @unlink(public_path($this->relVideo));
        parent::tearDown();
    }

    // ── Secure image endpoint ────────────────────────────────────────────

    public function test_secure_media_serves_valid_token()
    {
        $token = SecureMediaHelper::encode($this->relImage, 30);
        $res = $this->actingAs($this->user)->get('/secure-media?token=' . $token);
        $res->assertOk();
        $this->assertStringContainsString('image/png', $res->headers->get('Content-Type'));
        $this->assertStringContainsString('no-store', $res->headers->get('Cache-Control'));
    }

    public function test_secure_media_rejects_tampered_token()
    {
        $token = SecureMediaHelper::encode($this->relImage, 30);
        $this->actingAs($this->user)->get('/secure-media?token=' . $token . 'zzz')->assertStatus(403);
    }

    public function test_secure_media_rejects_expired_token()
    {
        // craft an already-expired token payload
        $expired = rtrim(strtr(\Illuminate\Support\Facades\Crypt::encryptString(json_encode([
            'p' => $this->relImage, 'e' => time() - 10,
        ])), '+/', '-_'), '=');
        $this->actingAs($this->user)->get('/secure-media?token=' . $expired)->assertStatus(403);
    }

    public function test_secure_media_rejects_path_traversal()
    {
        $token = SecureMediaHelper::encode('uploads/../.env', 30);
        $this->actingAs($this->user)->get('/secure-media?token=' . $token)->assertStatus(403);
    }

    public function test_secure_media_requires_login()
    {
        $token = SecureMediaHelper::encode($this->relImage, 30);
        $this->get('/secure-media?token=' . $token)->assertRedirect(route('login'));
    }

    // ── Admin HTML no longer leaks /uploads image paths ──────────────────

    public function test_admin_page_tokenizes_image_urls()
    {
        $cat = Category::create(['name' => 'SecCat', 'is_active' => true, 'order_index' => 1]);
        VideoLearningWord::create([
            'category_id' => $cat->id, 'title' => 'X',
            'video_path' => $this->relVideo, 'video_name' => 'clip.mp4',
            'thumbnail_path' => $this->relImage, 'json_data' => '{}', 'is_visible' => true,
        ]);

        $html = $this->actingAs($this->user)->get(route('video-learning.index'))->getContent();

        // No raw image path, and token URLs present instead.
        $this->assertStringNotContainsString($this->relImage, $html);
        $this->assertStringContainsString('secure-media?token=', $html);
    }

    // ── Secure video stream ──────────────────────────────────────────────

    public function test_video_stream_returns_bytes_for_authed_post()
    {
        $cat = Category::create(['name' => 'SecCat2', 'is_active' => true, 'order_index' => 1]);
        $v = VideoLearningWord::create([
            'category_id' => $cat->id, 'title' => 'V',
            'video_path' => $this->relVideo, 'video_name' => 'clip.mp4',
            'thumbnail_path' => $this->relImage, 'json_data' => '{}', 'is_visible' => true,
        ]);

        $res = $this->actingAs($this->user)->post(route('video-learning.stream', $v->id));
        $res->assertOk();
        $this->assertStringContainsString('video/mp4', $res->headers->get('Content-Type'));
        $this->assertStringContainsString('no-store', $res->headers->get('Cache-Control'));
    }

    public function test_video_stream_requires_login()
    {
        $cat = Category::create(['name' => 'SecCat3', 'is_active' => true, 'order_index' => 1]);
        $v = VideoLearningWord::create([
            'category_id' => $cat->id, 'title' => 'V',
            'video_path' => $this->relVideo, 'video_name' => 'clip.mp4',
            'thumbnail_path' => $this->relImage, 'json_data' => '{}', 'is_visible' => true,
        ]);

        $this->post(route('video-learning.stream', $v->id))->assertRedirect(route('login'));
    }

    public function test_video_stream_rejects_get()
    {
        $cat = Category::create(['name' => 'SecCat4', 'is_active' => true, 'order_index' => 1]);
        $v = VideoLearningWord::create([
            'category_id' => $cat->id, 'title' => 'V',
            'video_path' => $this->relVideo, 'video_name' => 'clip.mp4',
            'thumbnail_path' => $this->relImage, 'json_data' => '{}', 'is_visible' => true,
        ]);

        $this->actingAs($this->user)->get('/video-learning-words/' . $v->id . '/stream')->assertStatus(405);
    }
}
