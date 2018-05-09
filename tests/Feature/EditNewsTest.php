<?php

namespace Tests\Feature;

use App\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditNewsTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function unauthenticated_users_cant_update_existing_news()
    {
        $this->withExceptionHandling();

        $news = factory(News::class)->create();

        $this->postJson(route('news.update', $news))
            ->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_can_update_existing_news()
    {
        $news = factory(News::class)->create();

        $data = [
            'title' => 'Updated Title',
            'body' => 'Updated Body.'
        ];

        $this->actingAs($this->user)
            ->postJson(route('news.update', $news), $data)
            ->assertStatus(200);

        $news = $news->fresh();

        $this->assertEquals($data['title'], $news->title);
        $this->assertEquals($data['body'], $news->body);
    }

    /** @test */
    public function authenticated_users_can_replace_the_image_when_updating_existing_news()
    {
        Storage::fake('public');

        $data = [
            'title' => 'Some Title',
            'body' => 'News Article.',
            'image' => UploadedFile::fake()->image('image.jpg')
        ];

        $news = $this->actingAs($this->user)
            ->postJson(route('news.store'), $data)
            ->assertStatus(201)
            ->json();

        $data['image'] = $newFile = UploadedFile::fake()->image('new_image.jpg');

        $this->actingAs($this->user)
            ->postJson(route('news.update', $news['id']), $data)
            ->assertStatus(200);

        Storage::assertMissing($news['image_path']);

        $news = News::find($news['id']);

        $newImagePath = 'news/' . $newFile->hashName();

        $this->assertEquals($newImagePath, $news->image_path);

        Storage::assertExists($newImagePath);
    }
}
