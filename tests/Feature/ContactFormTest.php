<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function validData(Category $category, array $tagIds = []): array
    {
        return [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区千駄ヶ谷1-2-3',
            'building' => '千駄ヶ谷マンション305',
            'category_id' => $category->id,
            'detail' => '商品の届け日について',
            'tag_ids' => $tagIds,
        ];
    }

    /** @test */
    public function 入力ページにカテゴリとタグが表示される(): void
    {
        $category = Category::factory()->create(['content' => '商品のお届けについて']);
        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('contact.index');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee($category->content);
        $response->assertSee($tag->name);
    }

    /** @test */
    public function サンクスページが表示される(): void
    {
        $response = $this->get('/thanks');

        $response->assertOk();
        $response->assertViewIs('contact.thanks');
    }

    /** @test */
    public function 正しい入力なら確認ページに入力内容が表示される(): void
    {
        $category = Category::factory()->create(['content' => '商品のお届けについて']);
        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->post('/contacts/confirm', $this->validData($category, [$tag->id]));

        $response->assertOk();
        $response->assertViewIs('contact.confirm');
        $response->assertSee('山田');
        $response->assertSee('太郎');
        $response->assertSee('yamada@example.com');
        $response->assertSee('商品のお届けについて');
        $response->assertSee('質問');
    }

    /** @test */
    public function 確認ページへの送信で入力エラーがあれば入力ページに戻りエラーが返る(): void
    {
        $response = $this->from('/')->post('/contacts/confirm', []);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['first_name', 'last_name', 'gender', 'email', 'tel', 'address', 'category_id', 'detail']);
    }

    /** @test */
    public function 送信するとお問い合わせとタグが保存されサンクスページへ移動する(): void
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $response = $this->post('/contacts', $this->validData($category, $tags->pluck('id')->all()));

        $response->assertRedirect('/thanks');
        $this->assertDatabaseHas('contacts', [
            'email' => 'yamada@example.com',
            'category_id' => $category->id,
        ]);
        $this->assertDatabaseCount('contact_tag', 2);
        foreach ($tags as $tag) {
            $this->assertDatabaseHas('contact_tag', ['tag_id' => $tag->id]);
        }
    }

    /** @test */
    public function 送信で入力エラーがあれば保存されずエラーが返る(): void
    {
        $response = $this->from('/contacts/confirm')->post('/contacts', []);

        $response->assertRedirect('/contacts/confirm');
        $response->assertSessionHasErrors(['first_name', 'email', 'tel']);
        $this->assertDatabaseCount('contacts', 0);
    }
}
