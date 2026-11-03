<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateTagRequestTest extends TestCase
{
    use RefreshDatabase;

    private function rulesFor(Tag $tag): array
    {
        $request = new UpdateTagRequest;
        $request->setRouteResolver(function () use ($tag) {
            $route = new Route('PUT', 'admin/tags/{tag}', []);
            $route->bind(Request::create('/admin/tags/'.$tag->id, 'PUT'));
            $route->setParameter('tag', $tag);

            return $route;
        });

        return $request->rules();
    }

    /** @test */
    public function 自身の現在のタグ名のままなら受け付ける(): void
    {
        $tag = Tag::factory()->create(['name' => '質問']);
        $data = ['name' => '質問'];

        $validator = Validator::make($data, $this->rulesFor($tag));

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function 他のタグで使われている名前への変更は拒否する(): void
    {
        Tag::factory()->create(['name' => '要望']);
        $tag = Tag::factory()->create(['name' => '質問']);
        $data = ['name' => '要望'];

        $validator = Validator::make($data, $this->rulesFor($tag));

        $this->assertFalse($validator->passes());
    }
}
