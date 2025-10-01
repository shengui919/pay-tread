<script setup>
import { Link } from '@inertiajs/vue3'
defineProps({ loads: Object })
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">Loads</h1>
    <div class="bg-white shadow rounded">
      <table class="min-w-full">
        <thead class="bg-gray-50"><tr>
          <th class="px-4 py-2 text-left">Ref</th>
          <th class="px-4 py-2 text-left">Amount</th>
          <th class="px-4 py-2 text-left">Status</th>
          <th class="px-4 py-2"></th>
        </tr></thead>
        <tbody>
          <tr v-for="l in loads.data" :key="l.id" class="border-t">
            <td class="px-4 py-2">{{ l.ref }}</td>
            <td class="px-4 py-2">${{ (l.amount_cents/100).toFixed(2) }}</td>
            <td class="px-4 py-2"><span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ l.status }}</span></td>
            <td class="px-4 py-2 text-right">
              <Link :href="route('loads.show', l.id)" class="text-indigo-600 hover:underline">Open</Link>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="p-3 text-sm text-gray-500">Showing {{ loads.from }}–{{ loads.to }} of {{ loads.total }}</div>
    </div>
  </div>
</template>
