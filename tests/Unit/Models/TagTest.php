<?php

namespace Tests\Unit\Models;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 中間テーブルを介して1つのタグが複数のお問い合わせに紐づく(): void
    {
        $tag = Tag::factory()->create();
        $contacts = Contact::factory()->count(2)->create();
        foreach ($contacts as $contact) {
            $contact->tags()->attach($tag->id);
        }

        $result = $tag->contacts;

        $this->assertCount(2, $result);
        $this->assertInstanceOf(Contact::class, $result->first());
    }
}
