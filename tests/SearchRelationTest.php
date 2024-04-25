<?php

namespace AkkiIo\LaravelNovaSearch\Tests;

use AkkiIo\LaravelNovaSearch\LaravelNovaSearchable;
use AkkiIo\LaravelNovaSearch\Tests\Models\Post;
use AkkiIo\LaravelNovaSearch\Tests\Models\User;
use Illuminate\Support\Str;

use function factory;

class SearchRelationTest extends TestCase
{
    use LaravelNovaSearchable;

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'posts' => ['title'],
    ];

    /** @test */
    public function it_returns_results()
    {
        $user = factory(User::class)->create();

        $post = factory(Post::class)->create([
            'user_id' => $user->id,
        ]);

        $this->checkResults($post->title, 1);
    }

    /** @test */
    public function it_return_no_result()
    {
        $user = factory(User::class)->create();

        $post = factory(Post::class)->create([
            'user_id' => $user->id,
        ]);

        $this->checkResults($post->title.Str::random(), 0);

        $this->checkResults(Str::random(), 0);
    }
}
