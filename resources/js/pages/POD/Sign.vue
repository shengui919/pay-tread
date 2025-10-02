<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import SignaturePad from 'signature_pad'

const props = defineProps<{ load: any; rules: any }>()

const canvasRef = ref<HTMLCanvasElement | null>(null)
let sig: SignaturePad | null = null

const form = useForm({
  signer_name: '',
  signer_role: 'receiver',
  signature_png: '',
  lat: null as number | null,
  lng: null as number | null,
  accuracy_m: null as number | null,
  receiver_email: '',
  receiver_phone_e164: ''
})

function getGeo() {
  if (!navigator.geolocation) return
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      form.lat = pos.coords.latitude
      form.lng = pos.coords.longitude
      form.accuracy_m = Math.round(pos.coords.accuracy)
    },
    () => {},
    { enableHighAccuracy: true, timeout: 10000 }
  )
}

function clearSig() {
  sig?.clear()
}

function submit() {
  if (!sig || sig.isEmpty()) {
    alert('Please capture a signature')
    return
  }
  form.signature_png = sig.toDataURL('image/png')

  router.post(route('pod.submit', props.load.id), form, {
    onSuccess: () => router.visit(route('loads.show', props.load.id), { preserveScroll: true })
  })
}

onMounted(() => {
  if (canvasRef.value) {
    sig = new SignaturePad(canvasRef.value, { minWidth: 1, maxWidth: 2 })
  }
  getGeo()
})
</script>

<template>
  <div class="p-6 space-y-6">
    <Head :title="`Sign POD • Load ${props.load.ref}`" />

    <h1 class="text-2xl font-semibold">Sign POD — Load {{ props.load.ref }}</h1>

    <div v-if="props.load.bol_url" class="bg-white rounded shadow p-4">
      <div class="text-sm text-gray-500 mb-2">BOL Preview</div>
      <iframe :src="props.load.bol_url" class="w-full h-64 border rounded"></iframe>
    </div>

    <div class="bg-white rounded shadow p-4 space-y-3">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
          <label class="block text-sm mb-1">Signer name</label>
          <input v-model="form.signer_name" class="w-full border rounded p-2" />
        </div>
        <div>
          <label class="block text-sm mb-1">Signer role</label>
          <select v-model="form.signer_role" class="w-full border rounded p-2">
            <option value="receiver">Receiver</option>
            <option value="shipper_rep">Shipper Rep</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-sm mb-1">Signature</label>
        <div class="border rounded">
          <canvas ref="canvasRef" width="600" height="220" class="w-full"></canvas>
        </div>
        <button @click="clearSig" type="button" class="mt-2 text-sm text-gray-600 hover:text-gray-800">
          Clear
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
          <label class="block text-sm mb-1">Email (optional)</label>
          <input v-model="form.receiver_email" type="email" class="w-full border rounded p-2" />
        </div>
        <div>
          <label class="block text-sm mb-1">Phone (E.164)</label>
          <input v-model="form.receiver_phone_e164" placeholder="+1..." class="w-full border rounded p-2" />
        </div>
      </div>

      <div class="text-sm text-gray-500">
        Location accuracy:
        <b>{{ form.accuracy_m ?? '—' }}m</b>
        (need ≤ {{ props.rules.minAccuracyM }}m)
      </div>

      <div class="flex justify-end">
        <button @click="submit" type="button" class="px-4 py-2 bg-emerald-600 text-white rounded">
          Submit POD
        </button>
      </div>
    </div>
  </div>
</template>
