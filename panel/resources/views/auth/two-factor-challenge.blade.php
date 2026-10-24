@extends('layout')
@section('titulo', 'Verificación')
@section('contenido')
<div class="caja">
    <h1>Verificación en dos pasos</h1>
    <p class="sub">Escribe el código de 6 dígitos de tu app autenticadora.</p>
    <form method="POST" action="{{ url('/two-factor-challenge') }}">
        @csrf
        <label for="code">Código</label>
        <input id="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" autofocus>
        @error('code')<p class="error">{{ $message }}</p>@enderror
        <button type="submit">Verificar</button>
    </form>

    <h2>¿Perdiste el acceso a tu app?</h2>
    <form method="POST" action="{{ url('/two-factor-challenge') }}">
        @csrf
        <label for="recovery_code">Código de recuperación</label>
        <input id="recovery_code" type="text" name="recovery_code" autocomplete="off">
        @error('recovery_code')<p class="error">{{ $message }}</p>@enderror
        <button type="submit" class="sec">Usar código de recuperación</button>
    </form>
</div>
@endsection
