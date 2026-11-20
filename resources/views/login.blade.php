@extends('layout')
@section('body')<h1>Staff sign in</h1><section><form class="inline" method="post">@csrf<label>Email<input type="email" name="email" required></label><label>Password<input type="password" name="password" required></label><button>Sign in</button></form></section>@endsection
