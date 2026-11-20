@extends('layout')
@section('body')<h1>Front desk</h1>
<div class="quick" aria-label="Front desk shortcuts">
<a href="#move-in">Move in a tenant</a><a href="#stays">View stays</a><a href="#pending">Pending payments ({{ $pending->total() }})</a><a href="/reports">Open reports</a>
</div><section id="move-in"><h2>Move in a tenant</h2><form class="inline" method="post" action="/admit">@csrf<label>Tenant<select name="tenant" required>@foreach($tenants as $id=>$name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select></label><label>Room<select name="room" required>@foreach($rooms as $id=>$name)<option value="{{ $id }}" data-rate="{{ $rates[$id] }}">{{ $name }}</option>@endforeach</select></label><label>Rent plan<select name="plan"><option value="standard">Standard</option><option value="scholar">Scholar (10% off)</option></select></label><output id="estimate" aria-live="polite"></output><button>Move in</button></form></section>
<section id="stays"><div class="section-head"><h2>Stays</h2><form action="/#stays" class="inline"><label>Find a tenant or room<input type="search" name="search" value="{{ request('search') }}"></label><button>Search</button>@if(request()->filled('search'))<a href="/#stays">Clear</a>@endif</form></div><table><tr><th>Tenant / room</th><th>Moved in</th><th>Monthly rent</th><th>Actions</th></tr>@forelse($stays as $s)<tr><td>{{ $s->tenant }} · {{ $s->room }}</td><td>{{ $s->start }}</td><td>₱{{ number_format($s->monthly_rent) }}</td><td>@if($s->end)Moved out {{ $s->end }}@else<div class="actions">@foreach(['bill'=>'Bill this month','close'=>'Move out'] as $action=>$label)<form method="post" action="/stay/{{ $s->id }}/{{ $action }}">@csrf<button>{{ $label }}</button></form>@endforeach</div>@endif</td></tr>@empty<tr><td>{{ request()->filled('search')?'No stays match your search.':'No stays yet.' }}</td></tr>@endforelse</table>@include('pages',['rows'=>$stays])</section>
<section id="pending" aria-labelledby="pending-heading">
<h2 id="pending-heading">Pending payments</h2>
@forelse($pending as $person)
<details><summary>{{ $person->name }} · ₱{{ number_format($person->balance) }} unpaid</summary>
<table><thead><tr><th>Rent month</th><th>Room</th><th>Amount due</th><th>Payment</th></tr></thead><tbody>
@foreach($pendingDetails->get($person->id,collect()) as $bill)
<tr><td>{{ \Carbon\Carbon::parse($bill->month)->format('F Y') }}</td><td>{{ $bill->room }}</td><td>₱{{ number_format($bill->amount) }}</td>
<td><form method="post" action="/collect/{{ $bill->id }}" onsubmit="return confirm('Have you received the full amount for this bill?')">@csrf<button>Record payment</button></form></td></tr>
@endforeach</tbody></table></details>
@empty<p>All issued rent bills are paid.</p>@endforelse
@include('pages',['rows'=>$pending])
</section>
<section id="bills"><h2>Rent bills</h2><a href="{{ request('unpaid')?'/':'/?unpaid=1' }}">{{ request('unpaid')?'Show all bills':'Show unpaid only' }}</a><table><tr><th>Tenant</th><th>Month</th><th>Amount</th><th>Payment</th></tr>@forelse($bills as $b)<tr><td>{{ $b->tenant }}</td><td>{{ substr($b->month,0,7) }}</td><td>₱{{ number_format($b->amount) }}</td><td>@if($b->paid_on)Paid {{ $b->paid_on }}@else<form method="post" action="/collect/{{ $b->id }}" onsubmit="return confirm('Confirm full rent received?')">@csrf<button>Record payment</button></form>@endif</td></tr>@empty<tr><td>No bills to show.</td></tr>@endforelse</table>@include('pages',['rows'=>$bills])</section>@endsection
