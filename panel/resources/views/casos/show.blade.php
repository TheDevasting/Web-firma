@extends('layout')
@section('titulo', 'Caso '.$caso->referencia)
@section('contenido')
@include('barra')
<div class="caja ancho">
    <p><a href="{{ route('casos.index') }}">&larr; Volver a los casos</a></p>
    <h1>Caso <code>{{ $caso->referencia }}</code></h1>
    <p class="sub">{{ $caso->area->nombre }} · recibido el {{ $caso->created_at->format('d/m/Y H:i') }}</p>

    @if (session('status'))
        <p class="ok">{{ session('status') }}</p>
    @endif

    <h2>Datos del cliente</h2>
    <p><strong>Nombre:</strong> {{ $caso->nombre }}</p>
    <p><strong>Correo:</strong> {{ $caso->correo }}</p>
    <p><strong>Teléfono:</strong> {{ $caso->telefono }}</p>

    <h2>Detalles del caso</h2>
    <p>{!! nl2br(e($caso->mensaje)) !!}</p>
</div>
@endsection
