<?php

use App\Jobs\FindMatchingUsers;
use App\Models\Feed;
use App\Models\UpworkCategory;
use App\Models\UpworkJob;
use App\Models\User;
use App\Services\TelegramNotificationService;

function createUpworkJob(array $overrides = []): UpworkJob
{
    $defaults = [
        'id' => 'job-' . uniqid(),
        'title' => 'Laravel Vue dashboard improvements',
        'description' => 'Need a Laravel developer with Vue experience for admin panel changes.',
        'ciphertext' => 'abc123def456ghi789jkl',
        'duration' => 'Less than 1 month',
        'engagement' => '30+ hrs/week',
        'amount' => 1200,
        'hourlyBudgetType' => null,
        'hourlyBudgetMin' => null,
        'hourlyBudgetMax' => null,
        'premium' => false,
        'experience' => 'Intermediate',
        'client_hires' => 10,
        'client_jobs' => 20,
        'client_spent' => 5000,
        'client_verified' => true,
        'client_country' => 'United States',
        'client_reviews' => 6,
        'client_feedback' => 4.8,
        'created_at' => now(),
        'updated_at' => now(),
    ];

    return UpworkJob::create(array_merge($defaults, $overrides));
}

function createFeedForUser(User $user, array $overrides = []): Feed
{
    $defaults = [
        'user_id' => $user->id,
        'name' => 'Laravel Feed',
        'search_query' => null,
        'is_active' => true,
    ];

    return Feed::create(array_merge($defaults, $overrides));
}

test('it sends a telegram notification when category and keyword rules match', function () {
    $category = UpworkCategory::create([
        'label' => 'Web Development',
        'slug' => 'web_development',
    ]);

    $matchingUser = User::factory()->create(['telegram_id' => '1001']);
    $nonMatchingUser = User::factory()->create(['telegram_id' => '1002']);

    $matchingFeed = createFeedForUser($matchingUser, [
        'name' => 'Match Feed',
        'search_query' => [[
            'keywords' => ['laravel', 'vue'],
            'location' => ['title', 'description'],
            'condition' => 'all',
        ]],
    ]);

    $nonMatchingFeed = createFeedForUser($nonMatchingUser, [
        'name' => 'Keyword Miss Feed',
        'search_query' => [[
            'keywords' => ['symfony'],
            'location' => ['title', 'description'],
            'condition' => 'all',
        ]],
    ]);

    $matchingFeed->categories()->attach($category->id);
    $nonMatchingFeed->categories()->attach($category->id);

    $job = createUpworkJob(['upwork_category_id' => $category->id]);

    $telegramService = mock('overload:' . TelegramNotificationService::class);
    $telegramService
        ->shouldReceive('sendJobNotification')
        ->once()
        ->withArgs(function ($telegramId, UpworkJob $sentJob, Feed $sentFeed) use ($job, $matchingFeed) {
            return $telegramId === '1001'
                && $sentJob->id === $job->id
                && $sentFeed->id === $matchingFeed->id;
        })
        ->andReturn(true);

    (new FindMatchingUsers($job->id))->handle();
});

test('it does not send a telegram notification when selected category does not match', function () {
    $feedCategory = UpworkCategory::create([
        'label' => 'Mobile Development',
        'slug' => 'mobile_development',
    ]);

    $jobCategory = UpworkCategory::create([
        'label' => 'Data Science',
        'slug' => 'data_science',
    ]);

    $user = User::factory()->create(['telegram_id' => '2001']);

    $feed = createFeedForUser($user, [
        'search_query' => [[
            'keywords' => ['python'],
            'location' => ['title', 'description'],
            'condition' => 'any',
        ]],
    ]);

    $feed->categories()->attach($feedCategory->id);

    $job = createUpworkJob([
        'title' => 'Python ETL work',
        'description' => 'Build ETL pipelines with Python.',
        'upwork_category_id' => $jobCategory->id,
    ]);

    $telegramService = mock('overload:' . TelegramNotificationService::class);
    $telegramService->shouldNotReceive('sendJobNotification');

    (new FindMatchingUsers($job->id))->handle();
});

test('it does not send a telegram notification when keywords do not match', function () {
    $category = UpworkCategory::create([
        'label' => 'Web Development',
        'slug' => 'web_development_2',
    ]);

    $user = User::factory()->create(['telegram_id' => '3001']);

    $feed = createFeedForUser($user, [
        'search_query' => [[
            'keywords' => ['go', 'kubernetes'],
            'location' => ['title', 'description'],
            'condition' => 'all',
        ]],
    ]);

    $feed->categories()->attach($category->id);

    $job = createUpworkJob([
        'title' => 'Laravel app maintenance',
        'description' => 'Need help with Laravel queues and notifications.',
        'upwork_category_id' => $category->id,
    ]);

    $telegramService = mock('overload:' . TelegramNotificationService::class);
    $telegramService->shouldNotReceive('sendJobNotification');

    (new FindMatchingUsers($job->id))->handle();
});

