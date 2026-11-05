<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** @test */
    public function ログイン済みならタグ編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->actingAs($user)->get('/admin/tags/'.$tag->id.'/edit');

        $response->assertOk();
        $response->assertViewIs('admin.tags.edit');
        $response->assertSee('質問');
    }

    /** @test */
    public function ログイン済みならタグを作成でき管理画面へ移動する(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/tags', ['name' => '新しいタグ']);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', ['name' => '新しいタグ']);
    }

    /** @test */
    public function ログイン済みならタグを更新でき管理画面へ移動する(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->actingAs($user)->put('/admin/tags/'.$tag->id, ['name' => '質問（更新）']);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => '質問（更新）']);
    }

    /** @test */
    public function ログイン済みならタグを削除でき管理画面へ移動する(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($user)->delete('/admin/tags/'.$tag->id);

        $response->assertRedirect('/admin');
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    /** @test */
    public function 未ログインならタグ操作は拒否されログイン画面へ移動する(): void
    {
        $tag = Tag::factory()->create(['name' => '質問']);

        $this->get('/admin/tags/'.$tag->id.'/edit')->assertRedirect('/login');
        $this->post('/admin/tags', ['name' => '新しいタグ'])->assertRedirect('/login');
        $this->put('/admin/tags/'.$tag->id, ['name' => '変更'])->assertRedirect('/login');
        $this->delete('/admin/tags/'.$tag->id)->assertRedirect('/login');

        $this->assertDatabaseMissing('tags', ['name' => '新しいタグ']);
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => '質問']);
    }
}
