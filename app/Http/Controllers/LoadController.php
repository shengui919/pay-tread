<?php
namespace App\Http\Controllers;

use App\Models\Load;
use App\Services\Priority\PassportClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LoadController extends Controller {
    public function index() {
        $loads = Load::query()->latest()->select(['id','ref','amount_cents','status','carrier_id'])->paginate(15);
        return Inertia::render('Loads/Index', ['loads'=>$loads]);
    }
    public function show(Load $load) {
        $load->load(['pod','paymentIntent']);
        if ($load->pod) {
            $load->pod->bol_url = $load->pod->bol_path ? Storage::disk('s3')->temporaryUrl($load->pod->bol_path, now()->addMinutes(15)) : null;
            $load->pod->signed_bol_url = $load->pod->signed_bol_path ? Storage::disk('s3')->temporaryUrl($load->pod->signed_bol_path, now()->addMinutes(15)) : null;
        }
        return Inertia::render('Loads/Show', ['load'=>$load, 'env'=>['podLinkExpiryDays'=>(int) env('POD_LINK_EXPIRY_DAYS',7)]]);
    }
    public function createCheckoutLink(Request $r, Load $load, PassportClient $passport) {
        $this->authorize('update',$load);
        abort_unless($load->paymentIntent, 400, 'No payment intent');
        $checkout = $passport->createHostedCheckoutLink($load->paymentIntent->provider_ref, route('loads.show',$load->id));
        $load->paymentIntent->checkout_url = $checkout['url'] ?? null;
        $load->paymentIntent->save();
        return back()->with('flash',['type'=>'success','message'=>'Checkout link generated']);
    }
}
