@extends('layout')
@section('titulo', 'Casos')
@section('contenido')
@include('barra')
<div class="caja ancho">
    <h1>Panel de casos</h1>
    <p class="sub">Hola, {{ auth()->user()->name }}. Aquí aparecerán las consultas de tus áreas.</p>
    <p>(Próximamente: lista de casos y botón "Tomar caso".)</p>
</div>
@endsection
