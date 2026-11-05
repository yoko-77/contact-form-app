<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** @test */
    public function ログイン済みなら管理画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertViewIs('admin.index');
    }

    /** @test */
    public function 未ログインなら管理画面からログイン画面へ移動する(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 一覧は7件ごとにページネーションされる(): void
    {
        $user = User::factory()->create();
        Contact::factory()->count(8)->create();

        $page1 = $this->actingAs($user)->get('/admin');
        $page2 = $this->actingAs($user)->get('/admin?page=2');

        $page1->assertViewHas('contacts', fn ($contacts) => $contacts->count() === 7 && $contacts->total() === 8);
        $page2->assertViewHas('contacts', fn ($contacts) => $contacts->count() === 1);
    }

    /** @test */
    public function キーワードで名前とメールアドレスを部分一致で絞り込める(): void
    {
        $user = User::factory()->create();
        $byFirstName = Contact::factory()->create(['first_name' => '山田', 'last_name' => '太郎', 'email' => 'aaa@example.com']);
        $byLastName = Contact::factory()->create(['first_name' => '佐藤', 'last_name' => '花子', 'email' => 'bbb@example.com']);
        $byEmail = Contact::factory()->create(['first_name' => '鈴木', 'last_name' => '一郎', 'email' => 'suzuiti@example.com']);
        Contact::factory()->create(['first_name' => '高橋', 'last_name' => '次郎', 'email' => 'takahashi@example.com']);

        $firstNameResponse = $this->actingAs($user)->get('/admin?keyword=田');
        $lastNameResponse = $this->actingAs($user)->get('/admin?keyword=子');
        $emailResponse = $this->actingAs($user)->get('/admin?keyword=iti');

        $firstNameResponse->assertViewHas('contacts', fn ($contacts) => $contacts->pluck('id')->all() === [$byFirstName->id]);
        $lastNameResponse->assertViewHas('contacts', fn ($contacts) => $contacts->pluck('id')->all() === [$byLastName->id]);
        $emailResponse->assertViewHas('contacts', fn ($contacts) => $contacts->pluck('id')->all() === [$byEmail->id]);
    }

    /** @test */
    public function 性別で絞り込めて0なら全件になる(): void
    {
        $user = User::factory()->create();
        $male = Contact::factory()->create(['gender' => 1]);
        Contact::factory()->create(['gender' => 2]);

        $filtered = $this->actingAs($user)->get('/admin?gender=1');
        $all = $this->actingAs($user)->get('/admin?gender=0');

        $filtered->assertViewHas('contacts', fn ($contacts) => $contacts->pluck('id')->all() === [$male->id]);
        $all->assertViewHas('contacts', fn ($contacts) => $contacts->total() === 2);
    }

    /** @test */
    public function カテゴリで絞り込める(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $target = Contact::factory()->create(['category_id' => $category->id]);
        Contact::factory()->create();

        $response = $this->actingAs($user)->get('/admin?category_id='.$category->id);

        $response->assertViewHas('contacts', fn ($contacts) => $contacts->pluck('id')->all() === [$target->id]);
    }

    /** @test */
    public function 日付で絞り込める(): void
    {
        $user = User::factory()->create();
        $target = Contact::factory()->create();
        $target->forceFill(['created_at' => '2026-11-01 10:00:00'])->save();
        $other = Contact::factory()->create();
        $other->forceFill(['created_at' => '2026-11-02 10:00:00'])->save();

        $response = $this->actingAs($user)->get('/admin?date=2026-11-01');

        $response->assertViewHas('contacts', fn ($contacts) => $contacts->pluck('id')->all() === [$target->id]);
    }

    /** @test */
    public function 全ての検索条件を同時に指定して絞り込める(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();
        $base = ['first_name' => '山田', 'last_name' => '太郎', 'email' => 'aaa@example.com', 'gender' => 1, 'category_id' => $category->id];

        $target = Contact::factory()->create($base);
        $otherKeyword = Contact::factory()->create(array_merge($base, ['first_name' => '鈴木', 'email' => 'bbb@example.com']));
        $otherGender = Contact::factory()->create(array_merge($base, ['gender' => 2]));
        $otherCategoryContact = Contact::factory()->create(array_merge($base, ['category_id' => $otherCategory->id]));
        $otherDate = Contact::factory()->create($base);
        foreach ([$target, $otherKeyword, $otherGender, $otherCategoryContact] as $contact) {
            $contact->forceFill(['created_at' => '2026-11-01 10:00:00'])->save();
        }
        $otherDate->forceFill(['created_at' => '2026-11-02 10:00:00'])->save();

        $response = $this->actingAs($user)->get('/admin?keyword=山田&gender=1&category_id='.$category->id.'&date=2026-11-01');

        $response->assertViewHas('contacts', fn ($contacts) => $contacts->pluck('id')->all() === [$target->id]);
    }

    /** @test */
    public function 詳細ページにカテゴリ情報付きで表示される(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['content' => '商品トラブル']);
        $contact = Contact::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($user)->get('/admin/contacts/'.$contact->id);

        $response->assertOk();
        $response->assertViewIs('admin.show');
        $response->assertViewHas('contact', fn ($viewContact) => $viewContact->id === $contact->id);
        $response->assertSee('商品トラブル');
    }

    /** @test */
    public function お問い合わせを削除すると管理画面へ移動する(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create();

        $response = $this->actingAs($user)->delete('/admin/contacts/'.$contact->id);

        $response->assertRedirect('/admin');
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
}
