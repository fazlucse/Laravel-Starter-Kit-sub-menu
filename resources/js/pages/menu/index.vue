<template>
  <div>
    <h1>Menus</h1>
    <inertia-link href="/menus/create" class="btn btn-primary">Add Menu</inertia-link>

    <table class="table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Route</th>
          <th>Icon</th>
          <th>Order</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="menu in menus" :key="menu.id">
          <td>{{ menu.name }}</td>
          <td>{{ menu.route }}</td>
          <td>{{ menu.icon }}</td>
          <td>{{ menu.order }}</td>
          <td>
            <inertia-link :href="`/menus/${menu.id}/edit`" class="btn btn-sm btn-warning">Edit</inertia-link>
            <button @click="destroy(menu.id)" class="btn btn-sm btn-danger">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { Inertia } from '@inertiajs/vue3';
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const { props } = usePage();
const menus = ref(props.value.menus);

function destroy(id) {
  if (confirm('Are you sure?')) {
    Inertia.delete(`/menus/${id}`);
  }
}
</script>
