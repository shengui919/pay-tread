<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;

class SignedPodLinkMail extends Mailable {
    use Queueable;
    public function __construct(public string $loadRef, public string $url) {}
    public function build() {
        return $this->subject("Signed POD — Load {$this->loadRef}")
            ->view('emails.pod_link')->with(['loadRef'=>$this->loadRef,'url'=>$this->url]);
    }
}
