<?php
namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use App\Models\{Aircraft,Bid,Subfleet,User};
use App\Models\Enums\UserState;
use App\Services\FinanceService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Promethee\Services\ShopEntitlementService;

class ShopController extends Controller
{
    private const TYPES = [
        'aircraft_type','aircraft_variant','aircraft_premium','aircraft_rental',
        'reservation_extension','maintenance_priority',
        'hermes_theme','hermes_sound_pack','hermes_efb_skin','livery',
    ];

    public function index(Request $r)
    {
        $pilot=$r->user()->fresh('journal');
        $journal=$pilot->journal ?: $pilot->initJournal();
        $items=DB::table('promethee_shop_items')->where('active',true)->orderBy('type')->orderBy('price')->get();
        $orders=DB::table('promethee_shop_orders as orders')->join('promethee_shop_items as items','items.id','=','orders.item_id')
            ->where('orders.user_id',$pilot->id)->select('orders.*','items.name','items.type')->latest('purchased_at')->get();
        $bids=Bid::with('flight')->where('user_id',$pilot->id)->latest()->get();
        $aircraft=Aircraft::with('subfleet')->orderBy('registration')->get();

        return view('promethee::shop', compact('pilot','items','orders','bids','aircraft')+['wallet'=>$journal->getBalance()]);
    }

    public function buy(int $id, Request $r, FinanceService $finance, ShopEntitlementService $entitlements)
    {
        return DB::transaction(function() use($id,$r,$finance,$entitlements) {
            $item=DB::table('promethee_shop_items')->where('id',$id)->where('active',true)->lockForUpdate()->first();
            abort_unless($item,404);
            $user=$r->user()->fresh('journal');
            $journal=$user->journal ?: $user->initJournal();
            $targetId=$item->target_id ?: $r->input('target_id');
            $expiresAt=$item->duration_hours ? now()->addHours((int)$item->duration_hours) : null;

            if (in_array($item->type,['aircraft_type','aircraft_variant','aircraft_premium','aircraft_rental'],true)) {
                abort_unless($targetId && Subfleet::whereKey($targetId)->exists(),422,'Sous-flotte invalide.');
                if ($item->type !== 'aircraft_rental' && $entitlements->owns($user,$item->type,(string)$targetId))
                    return back()->withErrors(['shop'=>'Vous possédez déjà ce déblocage.']);
            }
            if ($item->type==='reservation_extension') {
                $bid=Bid::where('user_id',$user->id)->findOrFail($targetId);
                $expiresAt=now()->addHours((int)($item->duration_hours ?: 24));
            }
            if ($item->type==='maintenance_priority') {
                $plane=Aircraft::findOrFail($targetId);
            }

            $price=new Money((int)$item->price);
            if((int)$journal->getBalance()->getAmount() < (int)$price->getAmount())
                return back()->withErrors(['shop'=>'Solde phpVMS insuffisant.']);

            $finance->debitFromJournal($journal,$price,$user,'Boutique : '.$item->name,'shop','shop');
            $orderId=DB::table('promethee_shop_orders')->insertGetId([
                'user_id'=>$user->id,'item_id'=>$item->id,'target_id'=>$targetId,'price'=>$item->price,
                'purchased_at'=>now(),'expires_at'=>$expiresAt,'created_at'=>now(),'updated_at'=>now()
            ]);

            if ($item->type==='reservation_extension') {
                DB::table('promethee_shop_entitlements')->insert([
                    'user_id'=>$user->id,'item_id'=>$item->id,'type'=>$item->type,'target_type'=>'bid','target_id'=>$targetId,
                    'starts_at'=>now(),'expires_at'=>$expiresAt,'metadata'=>json_encode(['hours'=>(int)($item->duration_hours ?: 24)]),
                    'created_at'=>now(),'updated_at'=>now()
                ]);
            } elseif ($item->type==='maintenance_priority') {
                DB::table('promethee_maintenance_priorities')->insert([
                    'user_id'=>$user->id,'aircraft_id'=>$targetId,'order_id'=>$orderId,'status'=>'queued',
                    'requested_at'=>now(),'created_at'=>now(),'updated_at'=>now()
                ]);
            } else {
                DB::table('promethee_shop_entitlements')->insert([
                    'user_id'=>$user->id,'item_id'=>$item->id,'type'=>$item->type,'target_type'=>$item->target_type,
                    'target_id'=>$targetId,'starts_at'=>now(),'expires_at'=>$expiresAt,'metadata'=>$item->metadata,
                    'created_at'=>now(),'updated_at'=>now()
                ]);
            }

            return back()->with('success',$expiresAt ? 'Achat activé jusqu’au '.$expiresAt->format('d/m/Y H:i').'.' : 'Achat débloqué définitivement.');
        });
    }

    public function admin()
    {
        return view('promethee::admin-shop',[
            'items'=>DB::table('promethee_shop_items')->latest()->get(),
            'pilots'=>User::where('state',UserState::ACTIVE)->orderBy('pilot_id')->get(['id','pilot_id','name']),
            'subfleets'=>Subfleet::with('aircraft')->orderBy('name')->get(),
            'priorities'=>DB::table('promethee_maintenance_priorities as p')->join('aircraft','aircraft.id','=','p.aircraft_id')
                ->join('users','users.id','=','p.user_id')->select('p.*','aircraft.registration','users.pilot_id','users.name as pilot_name')
                ->latest('p.requested_at')->limit(50)->get(),
        ]);
    }

    public function store(Request $r)
    {
        $data=$r->validate([
            'name'=>'required|string|max:160','description'=>'nullable|string|max:5000',
            'type'=>'required|in:'.implode(',',self::TYPES),'price'=>'required|numeric|min:0|max:1000000',
            'target_id'=>'nullable|string|max:120','duration_hours'=>'nullable|integer|min:1|max:8760',
            'asset_key'=>'nullable|string|max:120','active'=>'nullable|boolean'
        ]);
        $aircraftTypes=['aircraft_type','aircraft_variant','aircraft_premium','aircraft_rental'];
        if(in_array($data['type'],$aircraftTypes,true)) {
            $r->validate(['target_id'=>'required|integer|exists:subfleets,id']);
        }
        if($data['type']==='aircraft_rental' && empty($data['duration_hours'])) {
            return back()->withErrors(['duration_hours'=>'Une location doit avoir une durée.'])->withInput();
        }
        $targetType=in_array($data['type'],$aircraftTypes,true) ? 'subfleet' : (
            str_starts_with($data['type'],'hermes_') ? 'hermes_asset' : ($data['type']==='livery' ? 'livery' : null)
        );
        DB::table('promethee_shop_items')->insert([
            'name'=>$data['name'],'description'=>$data['description']??null,'type'=>$data['type'],
            'target_type'=>$targetType,'target_id'=>$data['target_id']??($data['asset_key']??null),
            'price'=>Money::convertToSubunit($data['price']),'duration_hours'=>$data['duration_hours']??null,
            'metadata'=>isset($data['asset_key']) ? json_encode(['asset_key'=>$data['asset_key']]) : null,
            'active'=>$r->boolean('active'),'created_at'=>now(),'updated_at'=>now()
        ]);
        return back()->with('success','Produit ajouté à la boutique.');
    }

    public function priority(int $id, Request $r)
    {
        $data=$r->validate(['status'=>'required|in:queued,accepted,completed,rejected']);
        DB::table('promethee_maintenance_priorities')->where('id',$id)->update([
            'status'=>$data['status'],'processed_at'=>in_array($data['status'],['completed','rejected'],true)?now():null,'updated_at'=>now()
        ]);
        return back()->with('success','Priorité maintenance mise à jour.');
    }
}