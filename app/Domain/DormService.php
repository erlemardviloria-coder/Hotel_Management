<?php
namespace App\Domain;
use Illuminate\Support\Facades\DB;
final class DormService {
 public function log(string $description): void {
    DB::table('events')->insert(['description'=>$description,'user_id'=>auth()->id(),'created_at'=>now()]);
 }
 public function admit(int $tenant,int $roomId,string $plan): void {
    DB::transaction(function () use ($tenant,$roomId,$plan) {
        DB::table('tenants')->where('id',$tenant)->lockForUpdate()->firstOrFail();
        $room=DB::table('rooms')->where('id',$roomId)->lockForUpdate()->firstOrFail();
        $active=DB::table('stays')->whereNull('end');
        if ((clone $active)->where('tenant_id',$tenant)->exists()) throw new \DomainException('Tenant already has an active stay.');
        (new Occupancy($room->capacity,$active->where('room_id',$roomId)->count()))->admit();
        $rate=RentPlan::select($plan)->monthly($room->rent);
        $id=DB::table('stays')->insertGetId(['tenant_id'=>$tenant,'room_id'=>$roomId,'plan'=>$plan,'monthly_rent'=>$rate,'start'=>today()]);
        $this->issue($id,$rate);
        $this->log("Started stay #$id");
    },3);
 }
 private function issue(int $stay,int $rate): void {
    $month=today()->startOfMonth()->toDateString();
    if(DB::table('bills')->where(['stay_id'=>$stay,'month'=>$month])->exists()) throw new \DomainException('Rent already billed this month.');
    DB::table('bills')->insert(['stay_id'=>$stay,'month'=>$month,'amount'=>$rate]);
 }
 public function updateStay(int $id,string $action): void {
    DB::transaction(function () use ($id,$action) {
        $stay=DB::table('stays')->where('id',$id)->lockForUpdate()->firstOrFail();
        if($stay->end) throw new \DomainException('Stay already closed.');
        if($action==='bill') $this->issue($id,$stay->monthly_rent);
        else {
            if(DB::table('bills')->where('stay_id',$id)->whereNull('paid_on')->exists()) throw new \DomainException('Collect outstanding rent before move-out.');
            DB::table('stays')->where('id',$id)->update(['end'=>today()]);
        }
        $this->log("Stay #$id: $action");
    });
 }
 public function collect(int $id): void {
    DB::transaction(function () use ($id) {
        if(!DB::table('bills')->where('id',$id)->whereNull('paid_on')->update(['paid_on'=>today()])) throw new \DomainException('Bill unavailable or already paid.');
        $this->log("Collected bill #$id");
    });
 }
}
