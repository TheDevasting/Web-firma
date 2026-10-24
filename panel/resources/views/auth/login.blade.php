@extends('layout')
@section('titulo', 'Acceso')
@section('contenido')
<div class="caja">
    <h1>Acceso al panel</h1>
    <p class="sub">Solo para el equipo de la firma.</p>
    <form method="POST" action="{{ url('/login') }}">
        @csrf
        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        @error('email')<p class="error">{{ $message }}</p>@enderror

        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">

        <button type="submit">Iniciar sesión</button>
    </form>
</div>
@endsection
