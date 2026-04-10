<script setup lang="ts">
import { Alert, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { AlertCircleIcon } from 'lucide-vue-next';
import { ref } from 'vue';

import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css';

interface Props {
    composedPrompt: string;
    error: boolean;
}

const props = defineProps<Props>();
const copyLabel = ref('Copy');

const copyToClipboard = async () => {
    const text = props.composedPrompt.trim();

    if (!text) {
        copyLabel.value = 'Nothing to copy';
        setTimeout(() => {
            copyLabel.value = 'Copy';
        }, 1200);
        return;
    }

    await navigator.clipboard.writeText(text);
    copyLabel.value = 'Copied';

    toast.success('Prompt copied to clipboard!');

    setTimeout(() => {
        copyLabel.value = 'Copy';
    }, 1200);
};
</script>

<template>
    <Head title="Cover Letter" />
    <Toaster />

    <AppLayout>
        <div v-if="error" class="p-4">
            <Alert variant="destructive">
                <AlertCircleIcon />
                <AlertTitle>
                    Please set your cover letter prompt and applicant profile in <a href="/settings/ai" class="underline">AI settings</a> first.
                </AlertTitle>
            </Alert>
        </div>
        <div v-else class="p-4">
            <p class="mb-3">Copy the prompt below and use it in your preferred AI tool to generate a cover letter.</p>
            <div class="mb-3">
                <Button type="button" @click="copyToClipboard">{{ copyLabel }}</Button>
            </div>
            <Textarea :model-value="composedPrompt" rows="20" readonly @click="copyToClipboard" />
        </div>
    </AppLayout>
</template>
