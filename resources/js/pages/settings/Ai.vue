<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    applicant_profile: string;
    cover_letter_prompt: string;
}
const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'AI settings',
        href: '/settings/ai',
    },
];

const form = useForm({
    applicant_profile: props.applicant_profile,
    cover_letter_prompt: props.cover_letter_prompt,
});

const updateApplicantProfile = () => {
    form.patch(route('ai.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="AI settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="AI settings" description="Configure your AI settings" />

                <form @submit.prevent="updateApplicantProfile" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="applicant_profile">Applicant Profile</Label>
                        <Textarea
                            id="applicant_profile"
                            v-model="form.applicant_profile"
                            rows="6"
                            placeholder="structured short bio + bullets (skills, achievements)"
                        />
                        <InputError :message="form.errors.applicant_profile" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="cover_letter_prompt">Cover Letter Prompt</Label>
                        <Textarea
                            id="cover_letter_prompt"
                            v-model="form.cover_letter_prompt"
                            rows="6"
                            placeholder="structured short bio + bullets (skills, achievements)"
                        />
                        <InputError :message="form.errors.cover_letter_prompt" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
