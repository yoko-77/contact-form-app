<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function キーワード・性別・カテゴリ・日付のフィルタを受け付ける(): void
    {
        $category = Category::factory()->create();
        $data = [
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-11-01',
        ];

        $validator = Validator::make($data, (new IndexContactRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function 性別が0（全て）でも受け付ける(): void
    {
        $data = ['gender' => 0];

        $validator = Validator::make($data, (new IndexContactRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function 不正な性別の値を拒否する(): void
    {
        $data = ['gender' => 4];

        $validator = Validator::make($data, (new IndexContactRequest)->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('gender'));
    }
}
