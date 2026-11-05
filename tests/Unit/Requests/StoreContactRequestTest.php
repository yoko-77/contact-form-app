<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validData(): array
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        return [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区千駄ヶ谷1-2-3',
            'building' => '千駄ヶ谷マンション305',
            'category_id' => $category->id,
            'detail' => '商品の届け日について',
            'tag_ids' => $tags->pluck('id')->all(),
        ];
    }

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
    public function 全ての必須項目とタグ入力を受け付ける(int $gender): void
    {
        $data = array_merge($this->validData(), ['gender' => $gender]);

        $validator = Validator::make($data, (new StoreContactRequest)->rules());

        $this->assertTrue($validator->passes());
        $this->assertEquals($data, $validator->validated());
    }

    /** @test */
    public function ハイフン付きの電話番号を拒否する(): void
    {
        $data = array_merge($this->validData(), ['tel' => '03-12345678']);

        $validator = Validator::make($data, (new StoreContactRequest)->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('tel'));
    }

    /** @test */
    public function 桁数が足りない電話番号を拒否する(): void
    {
        $data = array_merge($this->validData(), ['tel' => '012345678']);

        $validator = Validator::make($data, (new StoreContactRequest)->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('tel'));
    }

    /** @test */
    public function 桁数が多すぎる電話番号を拒否する(): void
    {
        $data = array_merge($this->validData(), ['tel' => '090123456789']);

        $validator = Validator::make($data, (new StoreContactRequest)->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('tel'));
    }
}
