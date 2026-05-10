<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pause, Pencil, Play, Trash } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { index, create, edit, destroy, toggle } from '@/routes/feeds';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Feeds',
                href: index.url(),
            },
        ],
    },
});

interface Feed {
    id: number;
    name: string;
    search_query: object;
    is_active: boolean;
    created_at: string;
}

// Define props
defineProps<{
    feeds: Feed[];
}>();

// Methods
const deleteFeed = (id: number) => {
    if (confirm('Are you sure you want to delete this feed?')) {
        router.delete(destroy.url(id));
        // destroy.delete(id);
    }
};

const toggleFeedActive = (feed: Feed) => {
    // You may want to use a PATCH request to update is_active
    router.patch(toggle.url(feed.id), {
        is_active: !feed.is_active,
    });
};
</script>

<template>
    <Head title="Feeds" />

    <div class="p-4">
        <div class="mb-4">
            <Button as-child>
                <Link :href="create.url()">New Feed</Link>
            </Button>
        </div>

        <div v-if="feeds.length === 0" class="py-8 text-center text-gray-500">
            No feeds found. Create your first feed to get started!
        </div>
        <div v-else class="space-y-4">
            <template v-for="feed in feeds" :key="feed.id">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ feed.name }}</CardTitle>
                    </CardHeader>
                    <CardContent>qwerty</CardContent>
                    <CardFooter class="space-x-2">
                        <Button variant="outline" as-child>
                            <Link :href="edit.url(feed.id)">
                                <Pencil class="mr-2 h-4 w-4" />
                                Edit
                            </Link>
                        </Button>
                        <Button
                            variant="outline"
                            @click="toggleFeedActive(feed)"
                        >
                            <component
                                :is="feed.is_active ? Pause : Play"
                                class="mr-2 h-4 w-4"
                            />
                            {{ feed.is_active ? 'Pause' : 'Resume' }}
                        </Button>
                        <Button variant="outline" @click="deleteFeed(feed.id)">
                            <Trash class="mr-2 h-4 w-4" />
                            Delete
                        </Button>
                    </CardFooter>
                </Card>
            </template>
        </div>
    </div>
</template>
