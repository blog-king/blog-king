<?php

namespace Tests\Feature;

use App\User;
use Faker\Generator as Faker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ViewTest extends TestCase
{
    use DatabaseMigrations;

    private User $user;
    private Faker $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = $this->app->make(Faker::class);

        $this->user = factory(User::class)->create();
    }

    public function testHomepage()
    {
        $this->get(route('home'))->assertOk();
    }

    public function testBlogHomepage()
    {
        $this->get(route('user', ['name' => $this->user->name]))->assertOk();
    }
}
