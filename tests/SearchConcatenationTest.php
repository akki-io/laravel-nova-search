<?php

namespace AkkiIo\LaravelNovaSearch\Tests;

use AkkiIo\LaravelNovaSearch\LaravelNovaSearchable;
use AkkiIo\LaravelNovaSearch\Tests\Models\User;
use function factory;
use Illuminate\Support\Str;

class SearchConcatenationTest extends TestCase
{
    use LaravelNovaSearchable;

    /**
     * The columns that should be concatenated and searched.
     *
     * @var array
     */
    public static $searchConcatenation = [
        ['first_name', 'last_name'],
    ];

    /** @test */
    public function it_returns_results()
    {
        $user = factory(User::class)->create();

        $this->checkResults($user->first_name.' '.$user->last_name, 1);

        $this->checkResults($user->first_name, 1);
    }

    /** @test */
    public function it_returns_results_with_correct_escaping()
    {
        $user = factory(User::class)->create();
        $user->last_name = "D'Antonio";
        $user->save();

        $this->checkResults($user->first_name.' '.$user->last_name, 1);

        $this->checkResults($user->first_name, 1);
    }

    /** @test */
    public function it_return_no_result()
    {
        $user = factory(User::class)->create();

        $this->checkResults($user->last_name.' '.$user->first_name, 0);

        $this->checkResults($user->last_name.' '.Str::random(), 0);

        $this->checkResults(Str::random().' '.$user->last_name, 0);
    }
}
