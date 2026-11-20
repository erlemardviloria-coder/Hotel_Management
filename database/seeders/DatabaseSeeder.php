<?php
namespace Database\Seeders;
use Illuminate\Support\Facades\{DB,Hash};
class DatabaseSeeder extends \Illuminate\Database\Seeder {
 public function run(): void {
  foreach(['manager','clerk'] as $role) DB::table('users')->insertOrIgnore(['name'=>ucfirst($role),'email'=>$role.'@dorm.test','password'=>Hash::make('DormDemo2026!'),'role'=>$role]);
  DB::table('buildings')->insertOrIgnore(['name'=>'North Hall']);
  $hall=DB::table('buildings')->where('name','North Hall')->value('id');
  foreach(['N101','N102'] as $room) DB::table('rooms')->insertOrIgnore(['name'=>$room,'building_id'=>$hall,'capacity'=>2,'rent'=>2500]);
  DB::table('tenants')->insertOrIgnore(['name'=>'Alex Cruz','email'=>'alex@example.test']);
 }
}
