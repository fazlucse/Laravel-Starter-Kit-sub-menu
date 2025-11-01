<!-- resources/js/Components/DeleteDialog.vue -->
<template>
  <Dialog :open="isOpen" @update:open="isOpen = $event">
    <DialogTrigger as-child>
      <slot name="trigger">
        <button
          class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-900 dark:text-red-300 dark:hover:bg-red-800 transition"
          :title="`Delete ${recordName}`"
        >
          <Trash2 class="w-4 h-4" />
        </button>
      </slot>
    </DialogTrigger>

    <DialogContent>
      <form @submit.prevent="submit" class="space-y-6">
        <DialogHeader class="space-y-3">
          <DialogTitle>Delete {{ recordName }}?</DialogTitle>
          <DialogDescription>
            This action <strong>cannot be undone</strong>.
            Please enter a comment explaining why.
          </DialogDescription>
        </DialogHeader>

        <div class="grid gap-2">
          <Label for="comments" class="sr-only">Comment</Label>
          <Input
            id="comments"
            v-model="form.comments"
            placeholder="Reason for deletion (required)"
            autocomplete="off"
            required
            ref="commentInput"
            @input="form.clearErrors('comments')"
          />
          <InputError :message="form.errors.comments" />
        </div>

        <DialogFooter class="gap-2">
          <DialogClose as-child>
            <Button variant="secondary" type="button" @click="close">
              Cancel
            </Button>
          </DialogClose>

          <Button
            type="submit"
            variant="destructive"
            :disabled="form.processing"
            class="flex items-center gap-2"
          >
            <LoadingSpinner v-if="form.processing" />
            <span>{{ form.processing ? 'Deleting...' : 'Delete' }}</span>
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Trash2 } from 'lucide-vue-next'
import LoadingSpinner from '@/Components/custom/LoadingSpinner.vue'
import {
  Dialog,
  DialogTrigger,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
  DialogClose,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'

const props = defineProps<{
  url: string
  recordName: string
}>()

const emit = defineEmits<{
  (e: 'deleted'): void
}>()

const isOpen = ref(false)
const commentInput = ref<HTMLInputElement | null>(null)

// Fix: Send `comments` in body
const form = useForm({
  comments: '',
})

function submit() {
  form.transform((data) => ({
    ...data,
    _method: 'DELETE', // Laravel expects this for DELETE via POST
  })).post(props.url, {
    preserveScroll: true,
    onSuccess: () => {
      close()
      emit('deleted') // Triggers refresh in parent
    },
    onError: () => {
      commentInput.value?.focus()
    },
    onFinish: () => {
      form.reset('comments')
    },
  })
}

function close() {
  isOpen.value = false
  form.reset('comments')
  form.clearErrors()
}

watch(isOpen, (open) => {
  if (!open) close()
})
</script>