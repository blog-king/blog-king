<?php

namespace Tests\Feature;

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
