<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function タグ名が未入力の場合は拒否する(): void
    {
        $data = ['name' => ''];

        $validator = Validator::make($data, (new StoreTagRequest)->rules());

        $this->assertFalse($validator->passes());
    }

    /** @test */
    public function タグ名が50文字なら受け付ける(): void
    {
        $data = ['name' => str_repeat('あ', 50)];

        $validator = Validator::make($data, (new StoreTagRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function タグ名が51文字なら拒否する(): void
    {
        $data = ['name' => str_repeat('あ', 51)];

        $validator = Validator::make($data, (new StoreTagRequest)->rules());

        $this->assertFalse($validator->passes());
    }

    /** @test */
    public function 既に使われているタグ名は拒否する(): void
    {
        Tag::factory()->create(['name' => '質問']);
        $data = ['name' => '質問'];

        $validator = Validator::make($data, (new StoreTagRequest)->rules());

        $this->assertFalse($validator->passes());
    }
}
