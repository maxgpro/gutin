<?php

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->category = BlogCategory::factory()->create();
});

test('admin can create post with syntax highlighted code blocks', function () {
    $codeContent = '<pre><code class="language-javascript">function hello() {
    console.log("Hello, World!");
    return "Hello";
}</code></pre>';

    $response = $this->actingAs($this->admin)
        ->post(route('blog.posts.store'), [
            'title' => 'JavaScript Tutorial',
            'content' => '<p>Here is some JavaScript code:</p>' . $codeContent,
            'blog_category_id' => $this->category->id,
            'excerpt' => 'A tutorial about JavaScript',
            'status' => 'published',
        ]);

    $response->assertRedirect();

    $post = BlogPost::where('title', 'JavaScript Tutorial')->first();
    expect($post)->not->toBeNull();
    expect($post->content)->toContain('language-javascript');
    expect($post->content)->toContain('console.log');
});

test('code blocks with different languages are preserved', function () {
    $phpCode = '<pre><code class="language-php"><?php
class Example {
    public function test() {
        return "PHP Code";
    }
}</code></pre>';

    $pythonCode = '<pre><code class="language-python">def hello():
    print("Hello from Python")
    return True</code></pre>';

    $content = '<p>PHP example:</p>' . $phpCode . '<p>Python example:</p>' . $pythonCode;

    $response = $this->actingAs($this->admin)
        ->post(route('blog.posts.store'), [
            'title' => 'Multi-language Code Examples',
            'content' => $content,
            'blog_category_id' => $this->category->id,
            'excerpt' => 'Examples in multiple programming languages',
            'status' => 'published',
        ]);

    $response->assertRedirect();

    $post = BlogPost::where('title', 'Multi-language Code Examples')->first();
    expect($post)->not->toBeNull();
    expect($post->content)->toContain('language-php');
    expect($post->content)->toContain('language-python');
    expect($post->content)->toContain('class Example');
    expect($post->content)->toContain('def hello()');
});

test('post with code blocks displays correctly on show page', function () {
    $post = BlogPost::factory()->create([
        'blog_category_id' => $this->category->id,
        'user_id' => $this->admin->id,
        'title' => 'Code Tutorial',
        'content' => '<p>Example:</p><pre><code class="language-typescript">interface User {
    name: string;
    email: string;
}

const user: User = {
    name: "John",
    email: "john@example.com"
};</code></pre>',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $response = $this->get(route('blog.posts.show', $post));

    $response->assertOk();
    $response->assertSee('language-typescript', false);
    $response->assertSee('interface User', false);
    $response->assertSee('name: string', false);
});

test('code block language attribute is properly handled', function () {
    $codeBlocks = [
        'javascript' => '<pre><code class="language-javascript">const x = 42;</code></pre>',
        'php' => '<pre><code class="language-php"><?php echo "Hello"; ?></code></pre>',
        'python' => '<pre><code class="language-python">print("Hello")</code></pre>',
        'css' => '<pre><code class="language-css">.class { color: red; }</code></pre>',
    ];

    foreach ($codeBlocks as $language => $codeBlock) {
        $post = BlogPost::factory()->create([
            'blog_category_id' => $this->category->id,
            'user_id' => $this->admin->id,
            'title' => "Test {$language} Code",
            'content' => "<p>Example {$language} code:</p>{$codeBlock}",
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('blog.posts.show', $post));
        $response->assertSee("language-{$language}", false);
    }
});