<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewTest extends TestCase
{
    public function testHomepage()
    {
        $this->get('/')->assertOk();
    }

    public function testBlogHomepage()
    {
        $this->get('/blog/test')->assertOk();
    }
}
