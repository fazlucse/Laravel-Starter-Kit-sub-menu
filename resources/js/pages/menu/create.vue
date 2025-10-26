<template>
  <div>
    <h1>{{ menu ? 'Edit Menu' : 'Create Menu' }}</h1>

    <form @submit.prevent="submit">
      <div>
        <label>Name</label>
        <input v-model="form.name" required />
      </div>

      <div>
        <label>Route</label>
        <input v-model="form.route" />
      </div>

      <div>
        <label>Icon</label>
        <input v-model="form.icon" />
      </div>

      <div>
        <label>Order</label>
        <input type="number" v-model="form.order" />
      </div>

      <div>
        <label>Parent Menu</label>
        <select v-model="form.parent_id">
          <option :value="null">None</option>
          <option v-for="parent in parents" :key="parent.id" :value="parent.id">{{ parent.name }}</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">{{ menu ? 'Update' : 'Create' }}</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Inertia } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const { props } = usePage();
const menu = props.value.menu || null;
const parents = props.value.parents || [];

const form = ref({
  name: menu?.name || '',
  route: menu?.route || '',
  icon: menu?.icon || '',
  order: menu?.order || 0,
  parent_id: menu?.parent_id || null,
});

function submit() {
  if (menu) {
    Inertia.put(`/menus/${menu.id}`, form.value);
  } else {
    Inertia.post('/menus', form.value);
  }
}
</script>
