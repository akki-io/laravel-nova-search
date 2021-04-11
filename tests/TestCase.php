<?php

namespace AkkiIo\LaravelNovaSearch\Tests;

use AkkiIo\LaravelNovaSearch\Tests\Models\Post;
use AkkiIo\LaravelNovaSearch\Tests\Models\User;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // load and run the migration from this directory
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        // load model factory
        $this->withFactories(__DIR__.'/database/factories');
    }

    /**
     * Apply the search query to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function applySearch($query, $search)
    {
        return $query;
    }

    /**
     * Perform the search and check the result.
     *
     * @param $search
     * @param $count
     */
    protected function checkResults($search, $count)
    {
        $query = $this->applySearch((new User()), $search);

        $this->assertEquals($count, $query->count());
    }

    /**
     * Perform the search and check the result.
     *
     * @param $search
     * @param $count
     */
    protected function checkPostResults($search, $count)
    {
        $query = $this->applySearch((new Post()), $search);

        $this->assertEquals($count, $query->count());
    }
}
