<?php
namespace App\Http\Controllers;

use App\Models\Load;
use App\Models\Pod;
use App\Jobs\SharePodJob;
use App\Services\Geo\GeofenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PodController extends Controller {
    public function signPage(Load $load) {
        $this->authorize('view',$load);
        return Inertia::render('Pod/Sign', [
            'load'=>[
                'id'=>$load->id,'ref'=>$load->ref,
                'delivery_lat'=>$load->delivery_lat,'delivery_lng'=>$load->delivery_lng,
                'geofence_radius_m'=>$load->geofence_radius_m,
                'bol_url'=>$load->bol_path ? Storage::disk('s3')->temporaryUrl($load->bol_path, now()->addMinutes(15)) : null,
            ],
            'rules'=>['minAccuracyM'=>(int) env('MIN_GPS_ACCURACY_M',50)]
        ]);
    }

    public function submit(Request $req, Load $load, GeofenceService $geo) {
        $this->authorize('update',$load);
        $v = $req->validate([
            'signer_name'=>'required|string|max:120',
            'signer_role'=>'required|string|max:80',
            'signature_png'=>'required|string',
            'lat'=>'required|numeric','lng'=>'required|numeric',
            'accuracy_m'=>'nullable|integer|min:1',
            'receiver_email'=>'nullable|email',
            'receiver_phone_e164'=>'nullable|string|max:20',
        ]);

        $png = base64_decode(preg_replace('#^data:image/\w+;base64,#','',$v['signature_png']));
        $sigKey = "pods/{$load->id}/signature-".uniqid().".png";
        Storage::disk('s3')->put($sigKey, $png, 'private');

        $pod = Pod::updateOrCreate(['load_id'=>$load->id], [
            'type'=>'SIGNED_BOL','status'=>'submitted',
            'signer_name'=>$v['signer_name'],'signer_role'=>$v['signer_role'],
            'signature_png_path'=>$sigKey,'signature_hash'=>hash('sha256',$png),
            'lat'=>$v['lat'],'lng'=>$v['lng'],'accuracy_m'=>$v['accuracy_m'] ?? null,
            'receiver_email'=>$v['receiver_email'] ?? null,'receiver_phone_e164'=>$v['receiver_phone_e164'] ?? null,
            'submitted_at'=>now(),
        ]);

        $ok = $geo->within($v['lat'],$v['lng'], (float)$load->delivery_lat,(float)$load->delivery_lng, (int)($load->geofence_radius_m ?? env('GEOFENCE_RADIUS_M',250)))
              && (($v['accuracy_m'] ?? 9999) <= env('MIN_GPS_ACCURACY_M',50));

        if ($ok) { $pod->status='verified'; $pod->verified_at=now(); $pod->save(); return response()->json(['verified'=>true,'pod_id'=>$pod->id]); }
        $pod->save(); return response()->json(['verified'=>false,'pod_id'=>$pod->id,'reason'=>'Geofence/accuracy failed']);
    }

    public function share(Request $req, Pod $pod) {
        $this->authorize('view',$pod->load);
        $d = $req->validate([
            'channel'=>'required|in:email,sms',
            'recipients'=>'required|array|min:1','recipients.*'=>'string',
            'cc_broker'=>'boolean','cc_carrier'=>'boolean',
        ]);
        abort_unless($pod->status==='verified',400,'POD not verified');
        SharePodJob::dispatch($pod->id, $d['channel'], $d['recipients'], $d['cc_broker'] ?? false, $d['cc_carrier'] ?? false);
        $pod->increment('share_count'); $pod->last_shared_at=now(); $pod->save();
        return back()->with('flash',['type'=>'success','message'=>'POD is being sent.']);
    }
}
