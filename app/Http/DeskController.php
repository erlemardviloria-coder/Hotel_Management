<?php
namespace App\Http;
use App\Domain\DormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class DeskController {
 public function __construct(private DormService $service) {}
 public function records(Request $r,string $table) {
    $rows=DB::table($table)->where('name','like','%'.$r->query('q','').'%')->orderBy('name')->paginate(12)->withQueryString();
    $edit=$r->filled('edit') ? DB::table($table)->find($r->integer('edit')) : null;
    return view('records',compact('table','rows','edit')+['buildings'=>DB::table('buildings')->pluck('name','id')]);
 }
 public function save(Request $r,string $table,?int $id=null) {
    $unique=fn($field)=>Rule::unique($table,$field)->ignore($id);
    $rules=['name'=>['required','string','max:100']];
    if($table!=='tenants') $rules['name'][]=$unique('name');
    if($table==='tenants') $rules['email']=['required','email','max:180',$unique('email')];
    if($table==='rooms') $rules+=['building_id'=>'required|exists:buildings,id','capacity'=>'required|integer|between:1,100','rent'=>'required|integer|between:1,1000000'];
    $data=$r->validate($rules);
    DB::transaction(function() use($table,$id,$data){
        if($id){
            DB::table($table)->where('id',$id)->lockForUpdate()->firstOrFail();
            if($table==='rooms' && DB::table('stays')->where('room_id',$id)->whereNull('end')->count()>$data['capacity']) throw new \DomainException('Capacity is below current occupancy.');
            DB::table($table)->where('id',$id)->update($data);
        } else $id=DB::table($table)->insertGetId($data);
        $this->service->log("Saved $table #$id");
    });
    return redirect('/records/'.$table)->with('message','Record saved.');
 }
 public function remove(string $table,int $id){
    try {DB::transaction(function() use($table,$id){abort_unless(DB::table($table)->delete($id),404);$this->service->log("Deleted $table #$id");});}
    catch(\Illuminate\Database\QueryException $e){if(!in_array($e->getCode(),['23000','23503'])) throw $e;throw new \DomainException('This record is in use.');}
    return back()->with('message','Record deleted.');
 }
 public function home(Request $r){
    $openBills = DB::table('bills')
        ->join('stays','stays.id','=','bills.stay_id')
        ->join('tenants','tenants.id','=','stays.tenant_id')
        ->whereNull('bills.paid_on');
    $pending = (clone $openBills)->groupBy('tenants.id','tenants.name')
        ->select('tenants.id','tenants.name')
        ->selectRaw('SUM(bills.amount) as balance, MIN(bills.month) as oldest')
        ->orderBy('oldest')->orderBy('tenants.name')->orderBy('tenants.id')
        ->paginate(8,['*'],'pending')->withQueryString()->fragment('pending');
    $pendingDetails = $openBills->join('rooms','rooms.id','=','stays.room_id')
        ->whereIn('tenants.id',$pending->pluck('id'))
        ->orderBy('bills.month')->orderBy('bills.id')
        ->get(['tenants.id as tenant_id','rooms.name as room','bills.id','bills.month','bills.amount'])
        ->groupBy('tenant_id');
    $stays=DB::table('stays')->join('rooms','rooms.id','=','stays.room_id')->join('tenants','tenants.id','=','stays.tenant_id')->select('stays.*','rooms.name as room','tenants.name as tenant')->when($r->filled('search'),function($q) use($r){
        $term='%'.$r->string('search')->trim().'%';
        $q->where(fn($match)=>$match->where('tenants.name','like',$term)->orWhere('rooms.name','like',$term));
    })->orderByDesc('stays.id')->paginate(8,['*'],'s')->withQueryString();
    $bills=DB::table('bills')->join('stays','stays.id','=','bills.stay_id')->join('tenants','tenants.id','=','stays.tenant_id')->select('bills.*','tenants.name as tenant')->when($r->query('unpaid'),fn($q)=>$q->whereNull('paid_on'))->orderByDesc('bills.id')->paginate(8,['*'],'b')->withQueryString();
    return view('home',compact('stays','bills','pending','pendingDetails')+['rates'=>DB::table('rooms')->pluck('rent','id')]+['rooms'=>DB::table('rooms')->pluck('name','id'),'tenants'=>DB::table('tenants')->pluck('name','id')]);
 }
 public function admit(Request $r){
    $d=$r->validate(['tenant'=>'required|exists:tenants,id','room'=>'required|exists:rooms,id','plan'=>'required|in:standard,scholar']);
    $this->service->admit($d['tenant'],$d['room'],$d['plan']);return back()->with('message','Tenant moved in and first rent bill issued.');
 }
 public function stay(int $id,string $action){$this->service->updateStay($id,$action);return back()->with('message','Stay updated.');}
 public function collect(int $id){$this->service->collect($id);return back()->with('message','Payment recorded.');}
 public function reports(Request $r){
    $r->validate(['from'=>'sometimes|required|date_format:Y-m-d','to'=>'sometimes|required|date_format:Y-m-d|after_or_equal:from']);
    $from=$r->query('from',today()->startOfMonth()->toDateString());$to=$r->query('to',today()->toDateString());
    $collections=DB::table('bills')->whereBetween('paid_on',[$from,$to])->selectRaw('paid_on, count(*) as bills, sum(amount) as total')->groupBy('paid_on')->orderBy('paid_on')->get();
    $admissions=DB::table('stays')->join('rooms','rooms.id','=','stays.room_id')->whereBetween('start',[$from,$to])->selectRaw('rooms.name as room, count(*) as arrivals')->groupBy('rooms.name')->orderBy('rooms.name')->get();
    return view('reports',compact('from','to','collections','admissions'));
 }
 public function audit(){return view('audit',['events'=>DB::table('events')->join('users','users.id','=','events.user_id')->orderByDesc('events.id')->paginate(20)]);}
}
