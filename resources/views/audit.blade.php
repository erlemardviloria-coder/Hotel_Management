@extends('layout')
@section('body')<h1>Audit trail</h1><section><table><tr><th>Time</th><th>Staff</th><th>Event</th></tr>@forelse($events as $e)<tr><td>{{ $e->created_at }}</td><td>{{ $e->name }}</td><td>{{ $e->description }}</td></tr>@empty<tr><td>No changes recorded.</td></tr>@endforelse</table>@include('pages',['rows'=>$events])</section>@endsection
