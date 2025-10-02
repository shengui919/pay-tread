<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
const props = defineProps({ load: Object, env: Object })
const createCheckoutForm = useForm({})
const shareModalOpen = ref(false)
const shareChannel = ref('email')
const shareRecipients = ref('')
const checkoutUrl = computed(()=> props.load?.payment_intent?.checkout_url)
function generateCheckout(){ createCheckoutForm.post(route('loads.checkout-link', props.load.id)) }
function openShare(){ shareModalOpen.value = true }
function sendShare(){
  window.axios.post(route('pod.share', props.load.pod.id), {
    channel: shareChannel.value,
    recipients: shareRecipients.value.split(',').map(s=>s.trim()).filter(Boolean),
    cc_broker: true, cc_carrier: false
  }).then(()=>{ shareModalOpen.value=false })
}
</script>

<template>
  <Head :title="`Load ${load.ref}`" />
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Load {{ load.ref }}</h1>
      <Link :href="route('loads.index')" class="text-sm text-gray-600 hover:text-gray-800">Back</Link>
    </div>

    <div class="bg-white rounded shadow p-4">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm text-gray-500">Amount</div>
          <div class="text-xl font-semibold">${{ (load.amount_cents/100).toFixed(2) }}</div>
        </div>
        <div class="space-x-2">
          <button v-if="!checkoutUrl" @click="generateCheckout" class="px-3 py-2 bg-indigo-600 text-white rounded">Generate Checkout Link</button>
          <a v-else :href="checkoutUrl" target="_blank" class="px-3 py-2 bg-gray-100 rounded">Open Checkout</a>
        </div>
      </div>
      <div class="mt-3 text-sm">Payment status:
        <span class="px-2 py-1 bg-gray-100 rounded">{{ load.payment_intent?.status ?? 'n/a' }}</span>
      </div>
    </div>

    <div class="bg-white rounded shadow p-4">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold">Proof of Delivery</h2>
        <div>
          <span class="px-2 py-1 rounded text-xs"
            :class="{'bg-green-100 text-green-700': load.pod?.status==='verified',
                     'bg-yellow-100 text-yellow-700': load.pod?.status==='submitted',
                     'bg-red-100 text-red-700': load.pod?.status==='rejected'}">
            {{ load.pod?.status ?? 'missing' }}
          </span>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="col-span-2 space-y-2">
          <div class="text-sm text-gray-500">BOL</div>
          <div class="flex items-center space-x-2">
            <a v-if="load.pod?.signed_bol_url" :href="load.pod.signed_bol_url" class="text-indigo-600 hover:underline" target="_blank">Signed BOL</a>
            <a v-if="load.pod?.bol_url" :href="load.pod.bol_url" class="text-gray-600 hover:underline" target="_blank">Original</a>
            <span v-if="!load.pod">—</span>
          </div>
          <div class="text-sm text-gray-500 mt-3">Signer</div>
          <div class="text-sm">{{ load.pod?.signer_name }} <span class="text-gray-400">({{ load.pod?.signer_role }})</span></div>
        </div>

        <div class="space-y-2">
          <div class="text-sm text-gray-500">Actions</div>
          <Link :href="route('pod.sign.page', load.id)" class="px-3 py-2 bg-gray-100 rounded block text-center">Collect Signature</Link>
          <button v-if="load.pod && load.pod.status==='verified'" @click="openShare" class="px-3 py-2 bg-emerald-600 text-white rounded w-full">Share Signed POD</button>
        </div>
      </div>
    </div>
  </div>

  <div v-if="shareModalOpen" class="fixed inset-0 bg-black/40 flex items-center justify-center">
    <div class="bg-white rounded shadow p-5 w-full max-w-md">
      <h3 class="font-semibold mb-3">Share Signed POD</h3>
      <label class="block text-sm mb-1">Channel</label>
      <select v-model="shareChannel" class="w-full border rounded p-2 mb-3">
        <option value="email">Email</option><option value="sms">SMS</option>
      </select>
      <label class="block text-sm mb-1">Recipients (comma separated)</label>
      <input v-model="shareRecipients" class="w-full border rounded p-2 mb-4" placeholder="jane@shipper.com, +15555550123"/>
      <div class="flex justify-end space-x-2">
        <button @click="shareModalOpen=false" class="px-3 py-2 rounded bg-gray-100">Cancel</button>
        <button @click="sendShare" class="px-3 py-2 rounded bg-indigo-600 text-white">Send</button>
      </div>
      <p class="text-xs text-gray-400 mt-3">Links expire in {{ env.podLinkExpiryDays }} days.</p>
    </div>
  </div>
</template>
