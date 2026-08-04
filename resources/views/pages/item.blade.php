@extends('layouts.app', ['title' => 'Item — Tarkas'])

@section('content')
    <livewire:items.show :id="$id" />
@endsection
