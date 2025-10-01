<?php
namespace App\Jobs;

use App\Models\Pod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Twilio\Rest\Client as Twilio;

class SharePodJob implements ShouldQueue {
    use Queueable;

    public function __construct(
        public string $podId,
        public string $channel,
        public array $recipients,
        public bool $ccBroker=false,
        public bool $ccCarrier=false
    ) {}

    public function handle() {
        $pod = Pod::with('load')->findOrFail($this->podId);
        $key = $pod->signed_bol_path ?: $pod->bol_path;
        if (!$key) return;

        $url = Storage::disk('s3')->temporaryUrl($key, now()->addDays((int) env('POD_LINK_EXPIRY_DAYS',7)));

        if ($this->channel === 'email') {
            foreach ($this->recipients as $to) {
                Mail::to($to)->send(new \App\Mail\SignedPodLinkMail($pod->load->ref, $url));
            }
        } else {
            $tw = new Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'));
            foreach ($this->recipients as $to) {
                $tw->messages->create($to, ['from'=>env('TWILIO_FROM'),
                    'body'=>"PayTread: Signed POD for Load {$pod->load->ref}. {$url} (expires in 7 days)."]);
            }
        }
    }
}
