<?php
use Illuminate\Support\Facades\{Route,Auth,Gate};
use Illuminate\Http\Request;
use App\Http\DeskController as Desk;
Gate::define('manage',fn($user)=>$user->role==='manager');
Route::view('/login','login')->name('login');
Route::post('/login',function(Request $r){
 $credentials=$r->validate(['email'=>'required|email','password'=>'required|string']);
 if(!Auth::attempt($credentials)) return back()->withErrors('Email or password was incorrect.');
 $r->session()->regenerate(); return redirect('/');
})->middleware('throttle:8,1');
Route::middleware('auth')->group(function(){
 Route::post('/logout',function(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect('/login');});
 Route::get('/',[Desk::class,'home']);
 Route::get('/records/{table}',[Desk::class,'records'])->whereIn('table',['buildings','rooms','tenants']);
 Route::post('/records/{table}/{id?}',[Desk::class,'save'])->middleware('can:manage')->whereIn('table',['buildings','rooms','tenants'])->whereNumber('id');
 Route::delete('/records/{table}/{id}',[Desk::class,'remove'])->middleware('can:manage')->whereIn('table',['buildings','rooms','tenants'])->whereNumber('id');
 Route::post('/admit',[Desk::class,'admit']);
 Route::post('/stay/{id}/{action}',[Desk::class,'stay'])->whereIn('action',['bill','close']);
 Route::post('/collect/{id}',[Desk::class,'collect']);
 Route::get('/reports',[Desk::class,'reports']);
 Route::get('/audit',[Desk::class,'audit'])->middleware('can:manage');
});
