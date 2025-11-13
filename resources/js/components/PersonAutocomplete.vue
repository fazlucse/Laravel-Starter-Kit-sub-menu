<!-- resources/js/components/PersonAutocomplete.vue -->
<template>
  <div class="relative">
    <!-- Label -->
    <label class="block text-sm font-semibold text-gray-700 mb-1">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>

    <!-- Input -->
    <div class="relative">
      <input
        type="text"
        :value="displayValue"
        @input="onInput"
        @focus="onFocus"
        @blur="delayHide"
        :placeholder="`Search ${label.toLowerCase()}...`"
        :readonly="isLocked"
        class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        :class="{ 
          'border-red-500': error,
          'cursor-not-allowed': isLocked,
          'text-gray-900': selectedName,
          'text-gray-500': !selectedName && !search
        }"
        autocomplete="off"
      />

      <!-- Clear Button (X) -->
      <button
        v-if="selectedName"
        @click="clearSelection"
        class="cursor-pointer absolute inset-y-0 right-8 flex items-center pr-3 text-gray-400 hover:text-gray-600"
        type="button"
        title="Clear selection"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <!-- Spinner -->
      <div v-if="loading" class="absolute inset-y-0 right-0 flex items-center pr-3">
        <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
    </div>

    <!-- Dropdown: Multi-line (Name + Email) -->
    <div
      v-if="showDropdown && results.length"
      class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
    >
      <button
        v-for="item in results"
        :key="item.id"
        @mousedown.prevent="selectItem(item)"
        class="w-full px-4 py-2 text-left hover:bg-gray-100 flex items-center gap-3"
      >
        <img v-if="item.photo" :src="item.photo" class="w-8 h-8 rounded-full object-cover" />
        <div class="min-w-0 flex-1">
          <!-- Name: Bold -->
          <div class="font-medium text-gray-900 truncate">{{ item.name }}</div>
          <!-- Email: Small, gray -->
          <div v-if="item.email" class="text-xs text-gray-500 truncate">{{ item.email }}</div>
        </div>
      </button>
    </div>

    <!-- No Results -->
    <div
      v-if="showDropdown && !loading && !results.length && search"
      class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-3 text-sm text-gray-500"
    >
      No results found.
    </div>

    <!-- Error -->
    <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onBeforeUnmount, computed } from "vue";
import debounce from "lodash/debounce";
import api from "@/api";

interface Person {
  id: number;
  name: string;
  email?: string;
  photo?: string;
}

const props = defineProps<{
  modelValue: number | string | null;
  label: string;
  endpoint: string;
  required?: boolean;
  error?: string;
}>();

const emit = defineEmits<{
  (e: "update:modelValue", id: number | null): void;
  (e: "update:name", name: string): void;
}>();

const search       = ref("");
const results      = ref<Person[]>([]);
const showDropdown = ref(false);
const loading      = ref(false);
const selectedName = ref("");
const loadedId     = ref<number | null>(null);   // <-- Track real selection

let abortController: AbortController | null = null;

const cancelPrevious = () => {
  abortController?.abort();
  abortController = null;
};

/* --------------------------------------------------------------- */
/*  DISPLAY & LOCK                                                 */
/* --------------------------------------------------------------- */
const displayValue = computed(() => selectedName.value || search.value || "");
const isLocked     = computed(() => !!selectedName.value);

/* --------------------------------------------------------------- */
/*  SEARCH API                                                     */
/* --------------------------------------------------------------- */
const searchApi = debounce(async (query: string) => {
  if (!query.trim()) { results.value = []; return; }

  cancelPrevious();
  loading.value = true;
  abortController = new AbortController();

  try {
    const { data } = await api.get(props.endpoint, {
      params: { q: query },
      signal: abortController.signal,
    });
    results.value = Array.isArray(data) ? data : data[Object.keys(data)[0]] || [];
  } catch (err: any) {
    if (err.name !== "CanceledError") console.error(err);
    results.value = [];
  } finally {
    loading.value = false;
    abortController = null;
  }
}, 300);

/* --------------------------------------------------------------- */
/*  INPUT / FOCUS / BLUR                                           */
/* --------------------------------------------------------------- */
function onInput(e: Event) {
  if (isLocked.value) {
    e.preventDefault();   // ← Block typing after select
    return;
  }
  const val = (e.target as HTMLInputElement).value;
  search.value = val;
  searchApi(val);
}

function onFocus() {
  if (!isLocked.value) showDropdown.value = true;
}

function delayHide() {
  setTimeout(() => { showDropdown.value = false; }, 200);
}

/* --------------------------------------------------------------- */
/*  SELECT & CLEAR (only X clears)                                 */
/* --------------------------------------------------------------- */
function selectItem(item: Person) {
  emit("update:modelValue", item.id);
  emit("update:name", item.name);
  selectedName.value = item.name;
  search.value = "";
  showDropdown.value = false;
  loadedId.value = item.id;   // ← Mark as selected
}

function clearSelection() {
  emit("update:modelValue", null);
  emit("update:name", "");
  selectedName.value = "";
  search.value = "";
  results.value = [];
  showDropdown.value = false;
  loadedId.value = null;      // ← Only here we allow clearing
}

/* --------------------------------------------------------------- */
/*  LOAD SINGLE (edit mode)                                        */
/* --------------------------------------------------------------- */
const loadSingle = async (id: number | string) => {
  if (!id) return;

  const cached = results.value.find(r => r.id === Number(id));
  if (cached) {
    selectedName.value = cached.name;
    loadedId.value = Number(id);
    return;
  }

  loading.value = true;
  const ctrl = new AbortController();
  try {
    const { data } = await api.get(`${props.endpoint}/${id}`, { signal: ctrl.signal });
    if (data?.id === Number(id)) {
      selectedName.value = data.name;
      results.value = [data];
      loadedId.value = Number(id);
    }
  } catch (err: any) {
    if (err.name !== "CanceledError") console.error(err);
  } finally {
    loading.value = false;
  }
};

/* --------------------------------------------------------------- */
/*  WATCH modelValue – NEVER auto-clear on re-render               */
/* --------------------------------------------------------------- */
// watch(
//   () => props.modelValue,
//   (id) => {
//     const idNum = id ? Number(id) : null;

//     // 1. User clicked X → loadedId = null → allow clear
//     if (idNum === null && loadedId.value === null) {
//       selectedName.value = "";
//       search.value = "";
//       results.value = [];
//       return;
//     }

//     // 2. Same ID → do nothing (prevents flicker on tab switch)
//     if (idNum === loadedId.value) return;

//     // 3. New ID → load it
//     if (idNum) {
//       loadSingle(idNum);
//     }
//   },
//   { immediate: true }
// );

onBeforeUnmount(() => cancelPrevious());
</script>