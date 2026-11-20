<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dorm Desk | Residence management</title>
<link rel="stylesheet" href="{{ asset('bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('dorm-desk.css') }}">
<script defer src="{{ asset('desk-interactions.js') }}"></script></head><body>
<header><div class="mast"><div class="brand"><span class="mark" aria-hidden="true">D</span><div><strong>Dorm Desk</strong><small>Residence management</small></div></div>
@auth<div class="identity"><span>{{ auth()->user()->name }}</span><form method="post" action="/logout">@csrf<button>Sign out</button></form></div>@endauth</div>
@auth<nav aria-label="Main navigation">
@foreach(['Front desk'=>'/','Buildings'=>'/records/buildings','Rooms'=>'/records/rooms','Tenants'=>'/records/tenants','Reports'=>'/reports'] as $label=>$url)
<a href="{{ $url }}" @if(request()->getPathInfo()===$url) aria-current="page" @endif>{{ $label }}</a>
@endforeach
@can('manage')<a href="/audit" @if(request()->is('audit')) aria-current="page" @endif>Audit trail</a>@endcan</nav>@endauth</header>
<main>@if($errors->any())<p class="error" role="alert">{{ $errors->first() }}</p>@endif
@if(session('message'))<p class="notice" role="status">{{ session('message') }}</p>@endif
@yield('body')</main></body></html>
