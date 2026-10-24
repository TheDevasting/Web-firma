@extends('layout')
@section('titulo', 'Confirmar contraseña')
@section('contenido')
<div class="caja">
    <h1>Confirma tu contraseña</h1>
    <p class="sub">Es una zona segura: necesitamos confirmar que eres tú.</p>
    <form method="POST" action="{{ url('/user/confirm-password') }}">
        @csrf
        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password" required autofocus autocomplete="current-password">
        @error('password')<p class="error">{{ $message }}</p>@enderror
        <button type="submit">Confirmar</button>
    </form>
</div>
@endsection
