<?php
namespace App\Traits;

use App\Models\Post;
use App\Models\PostQueue;

trait Publishable
{
    /**
     * Get all posts for this user.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get queued posts.
     */
    public function queuedPosts()
    {
        return $this->hasMany(PostQueue::class);
    }

    /**
     * Get published posts.
     */
    public function publishedPosts()
    {
        return $this->posts()->where('status', 'published');
    }

    /**
     * Get draft posts.
     */
    public function draftPosts()
    {
        return $this->posts()->where('status', 'draft');
    }

    /**
     * Get failed posts.
     */
    public function failedPosts()
    {
        return $this->posts()->where('status', 'failed');
    }

    /**
     * Get scheduled posts.
     */
    public function scheduledPosts()
    {
        return $this->posts()->where('status', 'scheduled');
    }

    /**
     * Create a new post.
     */
    public function createPost($data)
    {
        return $this->posts()->create($data);
    }

    /**
     * Queue a post for publishing.
     */
    public function queuePost($postId, $platforms = [], $scheduledFor = null)
    {
        $post = $this->posts()->find($postId);
        
        if (!$post) {
            return false;
        }

        return PostQueue::create([
            'user_id' => $this->id,
            'post_id' => $post->id,
            'platforms' => $platforms,
            'scheduled_for' => $scheduledFor ?: now(),
            'status' => 'pending'
        ]);
    }
}