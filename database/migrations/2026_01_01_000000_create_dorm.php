<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('users', function(Blueprint $t){$t->id();$t->string('name');$t->string('email')->unique();$t->string('password');$t->string('role');$t->timestamps();});
  Schema::create('buildings', function(Blueprint $t){$t->id();$t->string('name')->unique();});
  Schema::create('rooms', function(Blueprint $t){$t->id();$t->foreignId('building_id')->constrained();$t->string('name')->unique();$t->unsignedInteger('capacity');$t->unsignedInteger('rent');});
  Schema::create('tenants', function(Blueprint $t){$t->id();$t->string('name');$t->string('email')->unique();});
  Schema::create('stays', function(Blueprint $t){$t->id();$t->foreignId('room_id')->constrained();$t->foreignId('tenant_id')->constrained();$t->date('start');$t->date('end')->nullable();$t->unsignedInteger('monthly_rent');$t->string('plan');});
  Schema::create('bills', function(Blueprint $t){$t->id();$t->foreignId('stay_id')->constrained();$t->date('month');$t->unsignedInteger('amount');$t->date('paid_on')->nullable();$t->unique(['stay_id','month']);});
  Schema::create('events', function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained();$t->string('description');$t->timestamp('created_at');});
 }
 public function down(): void {foreach(['events','bills','stays','tenants','rooms','buildings','users'] as $name) Schema::dropIfExists($name);}
};
