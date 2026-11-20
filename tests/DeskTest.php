<?php
use App\Domain\DormService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\TestCase;
final class DeskTest extends TestCase {
 public function createApplication(): \Illuminate\Foundation\Application {
    $app=require dirname(__DIR__).'/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    return $app;
 }
 protected function setUp(): void {
    parent::setUp();$this->artisan('migrate');$this->seed(Database\Seeders\DatabaseSeeder::class);$this->actingAs(User::firstOrFail());
 }
 public function test_full_stay_and_views(): void {
    $this->get('/')->assertOk();$this->post('/admit',['tenant'=>1,'room'=>1,'plan'=>'scholar'])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('bills',['amount'=>2250]);
    $this->post('/stay/1/close')->assertSessionHasErrors();
    $this->post('/collect/1')->assertRedirect();$this->post('/stay/1/close')->assertRedirect();
    $this->assertDatabaseMissing('stays',['id'=>1,'end'=>null]);
    foreach(['/','/records/rooms','/records/buildings','/records/tenants','/reports','/audit','/login'] as $url) $this->get($url)->assertOk();
 }
 public function test_room_limit_and_duplicate_billing(): void {
    DB::table('rooms')->where('id',1)->update(['capacity'=>1]);
    $desk=app(DormService::class);$desk->admit(1,1,'standard');
    $this->assertDatabaseHas('bills',['amount'=>2500]);
    $this->post('/stay/1/bill')->assertSessionHasErrors();
    $this->post('/admit',['tenant'=>1,'room'=>2,'plan'=>'standard'])->assertSessionHasErrors();
    $id=DB::table('tenants')->insertGetId(['name'=>'Sam','email'=>'sam@example.test']);
    $this->post('/admit',['tenant'=>$id,'room'=>1,'plan'=>'standard'])->assertSessionHasErrors();
    $this->assertDatabaseCount('stays',1);$this->assertDatabaseCount('bills',1);
    $desk->collect(1);$this->expectException(DomainException::class);$desk->collect(1);
 }
 public function test_staff_permissions_and_validation(): void {
    $this->post('/records/buildings',['name'=>'East Hall'])->assertRedirect();
    $this->post('/records/buildings/2',['name'=>'West Hall'])->assertRedirect();
    $this->assertDatabaseHas('buildings',['name'=>'West Hall']);
    $this->delete('/records/buildings/2')->assertRedirect();$this->assertDatabaseCount('buildings',1);
    $this->actingAs(User::where('role','clerk')->firstOrFail());
    $this->post('/records/buildings',['name'=>'Forbidden'])->assertForbidden();
    $this->get('/audit')->assertForbidden();
 }
}
