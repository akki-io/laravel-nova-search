<?php

namespace AkkiIo\LaravelNovaSearch\Tests;

use AkkiIo\LaravelNovaSearch\LaravelNovaSearchable;
use AkkiIo\LaravelNovaSearch\Tests\Models\Post;
use AkkiIo\LaravelNovaSearch\Tests\Models\User;
use function factory;
use Illuminate\Support\Str;

class SearchRelationMatchingAnyTest extends TestCase
{
    use LaravelNovaSearchable;

    /**
     * The relationship columns that should be searched for any matching entry.
     *
     * @var array
     */
    public static $searchRelationsMatchingAny = [
        'user' => ['first_name', 'last_name'],
    ];

    /** @test */
    public function it_returns_results()
    {
        $user = factory(User::class)->create();

        factory(Post::class)->create([
            'user_id' => $user->id,
        ]);

        $this->checkPostResults($user->first_name.' '.$user->last_name, 1);

        $this->checkPostResults($user->last_name.' '.$user->first_name, 1);

        $this->checkPostResults($user->last_name.' '.$user->email, 1);

        $this->checkPostResults($user->first_name.' '.Str::random(), 1);
    }

    /** @test */
    public function it_return_no_result()
    {
        $user = factory(User::class)->create();

        factory(Post::class)->create([
            'user_id' => $user->id,
        ]);

        $this->checkPostResults($user->email.' '.$user->company, 0);

        $this->checkPostResults($user->email.' '.Str::random(), 0);

        $this->checkPostResults(Str::random(), 0);
    }
}
