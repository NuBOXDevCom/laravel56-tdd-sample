<?php

namespace Tests\Feature;

use App\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateNewsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_users_can_create_new_news()
    {
        $this->assertEquals(0, News::count());

        $data = [
            'title' => 'Some Title',
            'body' => 'News Article.'
        ];

        $this->actingAs($this->user)
            ->postJson(route('news.store'), $data)
            ->assertStatus(201);

        $this->assertEquals(1, News::count());

        $news = News::first();

        $this->assertEquals($data['title'], $news->title);
        $this->assertEquals($data['body'], $news->body);
    }

    /** @test */
    public function unauthenticated_users_cant_create_new_news()
    {
        $this->postJson(route('news.store'), [])
            ->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_can_create_news_with_images()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('image.jpg');

        $data = [
            'title' => 'Some Title',
            'body' => 'News Article.',
            'image' => $file
        ];

        $this->actingAs($this->user)
            ->postJson(route('news.store'), $data)
            ->assertStatus(201);

        $news = News::first();

        $imagePath = 'news/' . $file->hashName();

        $this->assertEquals($imagePath, $news->image_path);
        Storage::assertExists($imagePath);
    }
}
