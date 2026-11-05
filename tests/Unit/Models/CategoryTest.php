<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function カテゴリから紐づく複数のお問い合わせを取得できる(): void
    {
        $category = Category::factory()->create();
        Contact::factory()->count(2)->create(['category_id' => $category->id]);

        $contacts = $category->contacts;

        $this->assertCount(2, $contacts);
        $this->assertInstanceOf(Contact::class, $contacts->first());
    }
}
