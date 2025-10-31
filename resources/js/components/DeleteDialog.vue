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
          >
            {{ form.processing ? 'Deleting...' : 'Delete' }}
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

// Props
const props = defineProps<{
  url: string
  recordName: string
}>()

// Emits
const emit = defineEmits<{
  (e: 'deleted'): void
}>()

// Control dialog open state
const isOpen = ref(false)

// Form
const form = useForm({
  comments: '',
})

const commentInput = ref<HTMLInputElement | null>(null)

// Submit delete
function submit() {
  form.delete(props.url, {
    data: { comments: form.comments },
    preserveScroll: true,
    onSuccess: () => {
      emit('deleted')
      close() // Close dialog on success
    },
    onError: () => {
      commentInput.value?.focus()
    },
    onFinish: () => {
      form.reset('comments')
    },
  })
}

// Close dialog (Cancel or after success)
function close() {
  isOpen.value = false
  form.reset('comments')
  form.clearErrors()
}

// Optional: Close on escape (already handled by Dialog, but safe)
watch(isOpen, (open) => {
  if (!open) {
    close()
  }
})
</script>