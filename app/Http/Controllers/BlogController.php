<?php

namespace App\Http\Controllers;

use App\Models\Post;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::published()->with('translations', 'media')
            ->orderByDesc('is_featured')->latest('published_at')->latest()->paginate(9);

        $featured = Post::published()->where('is_featured', true)
            ->with('translations', 'media')->latest('published_at')->first();

        return view('blog.index', compact('posts', 'featured'));
    }

    public function show(string $slug)
    {
        $post = Post::where('slug', $slug)->published()
            ->with('translations', 'media', 'author')->firstOrFail();

        $post->increment('views');

        $related = Post::published()->where('id', '!=', $post->id)
            ->where('category', $post->category)->with('translations', 'media')->take(3)->get();

        $seo = [
            'title' => $post->translation()?->meta_title ?: $post->translation()?->title,
            'description' => $post->translation()?->meta_description ?: $post->translation()?->excerpt,
        ];

        return view('blog.show', compact('post', 'related', 'seo'));
    }
}
