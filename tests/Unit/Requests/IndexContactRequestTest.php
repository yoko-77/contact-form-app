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

    public static function genderProvider(): array
    {
        return [
            '男性' => [1],
            '女性' => [2],
            'その他' => [3],
        ];
    }

    /**
     * @test
     *
     * @dataProvider genderProvider
     */
    public function キーワード・性別・カテゴリ・日付のフィルタを受け付ける(int $gender): void
    {
        $category = Category::factory()->create();
        $data = [
            'keyword' => '山田',
            'gender' => $gender,
            'category_id' => $category->id,
            'date' => '2026-11-01',
        ];

        $validator = Validator::make($data, (new IndexContactRequest)->rules());

        $this->assertTrue($validator->passes());
        $this->assertEquals($data, $validator->validated());
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
