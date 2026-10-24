@extends('layout')
@section('titulo', 'Cambiar contraseña')
@section('contenido')
@if (! auth()->user()->debe_cambiar_clave)
    @include('barra')
@endif
<div class="caja">
    <h1>Cambiar contraseña</h1>
    @if (auth()->user()->debe_cambiar_clave)
        <p class="sub">Entraste con una clave temporal. Elige una contraseña propia para continuar.</p>
    @else
        <p class="sub">Mínimo 12 caracteres, con mayúsculas, minúsculas y números.</p>
    @endif

    <form method="POST" action="{{ route('clave.actualizar') }}">
        @csrf
        @method('PUT')
        <label for="actual">Contraseña actual</label>
        <input id="actual" type="password" name="actual" required autocomplete="current-password">
        @error('actual')<p class="error">{{ $message }}</p>@enderror

        <label for="nueva">Contraseña nueva</label>
        <input id="nueva" type="password" name="nueva" required autocomplete="new-password">
        @error('nueva')<p class="error">{{ $message }}</p>@enderror

        <label for="nueva_confirmation">Repite la contraseña nueva</label>
        <input id="nueva_confirmation" type="password" name="nueva_confirmation" required autocomplete="new-password">

        <button type="submit">Guardar contraseña</button>
    </form>
    @if (auth()->user()->debe_cambiar_clave)
        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button type="submit" class="sec">Cerrar sesión</button>
        </form>
    @endif
</div>
@endsection
