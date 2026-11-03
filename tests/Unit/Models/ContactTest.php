<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function お問い合わせは特定のカテゴリに属する(): void
    {
        $category = Category::factory()->create();
        $contact = Contact::factory()->create(['category_id' => $category->id]);

        $result = $contact->category;

        $this->assertInstanceOf(Category::class, $result);
        $this->assertSame($category->id, $result->id);
    }

    /** @test */
    public function お問い合わせは複数のタグと同期できる(): void
    {
        $contact = Contact::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        $contact->tags()->sync($tags->pluck('id')->all());

        $this->assertCount(3, $contact->fresh()->tags);

        $contact->tags()->sync([$tags->first()->id]);

        $this->assertCount(1, $contact->fresh()->tags);
    }
}
