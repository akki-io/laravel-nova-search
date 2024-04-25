<?php

namespace AkkiIo\LaravelNovaSearch\Tests;

use AkkiIo\LaravelNovaSearch\LaravelNovaSearchable;
use AkkiIo\LaravelNovaSearch\Tests\Models\User;
use Illuminate\Support\Str;

use function factory;

class SearchMatchingAnyTest extends TestCase
{
    use LaravelNovaSearchable;

    /**
     * The columns that should be searched for any matching entry.
     *
     * @var array
     */
    public static $searchMatchingAny = [
        'first_name',
        'last_name',
    ];

    /** @test */
    public function it_returns_results()
    {
        $user = factory(User::class)->create();

        $this->checkResults($user->first_name.' '.$user->last_name, 1);

        $this->checkResults($user->last_name.' '.$user->first_name, 1);

        $this->checkResults($user->last_name.' '.$user->email, 1);

        $this->checkResults($user->first_name.' '.Str::random(), 1);
    }

    /** @test */
    public function it_return_no_result()
    {
        $user = factory(User::class)->create();

        $this->checkResults($user->email.' '.$user->company, 0);

        $this->checkResults($user->email.' '.Str::random(), 0);

        $this->checkResults(Str::random(), 0);
    }
}
