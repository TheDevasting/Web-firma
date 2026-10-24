@extends('layout')
@section('titulo', 'Seguridad')
@section('contenido')
@include('barra')
@php
    $u = auth()->user();
    $mensajes = [
        'two-factor-authentication-enabled'   => 'Escanea el código QR y confirma con un código para terminar.',
        'two-factor-authentication-confirmed' => 'Verificación en dos pasos activada. Guarda tus códigos de recuperación.',
        'two-factor-authentication-disabled'  => 'Verificación en dos pasos desactivada.',
        'recovery-codes-generated'            => 'Se generaron códigos de recuperación nuevos.',
    ];
@endphp
<div class="caja ancho">
    <h1>Seguridad de la cuenta</h1>
    <p class="sub">Verificación en dos pasos (obligatoria para usar el panel).</p>

    @if (session('status'))
        <p class="ok">{{ $mensajes[session('status')] ?? session('status') }}</p>
    @endif

    @if (! $u->two_factor_secret)
        <p>Todavía no está activada. Necesitarás una app autenticadora en tu celular (Google Authenticator, Microsoft Authenticator u otra).</p>
        <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
            @csrf
            <button type="submit">Activar verificación en dos pasos</button>
        </form>

    @elseif (! $u->two_factor_confirmed_at)
        <h2>1. Escanea este código QR con tu app</h2>
        <div class="qr">{!! $u->twoFactorQrCodeSvg() !!}</div>
        <p>¿No puedes escanearlo? Clave manual: <code>{{ decrypt($u->two_factor_secret) }}</code></p>

        <h2>2. Escribe el código que muestra la app</h2>
        <form method="POST" action="{{ url('/user/confirmed-two-factor-authentication') }}">
            @csrf
            <label for="code">Código de 6 dígitos</label>
            <input id="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required>
            @if ($errors->confirmTwoFactorAuthentication->has('code'))
                <p class="error">{{ $errors->confirmTwoFactorAuthentication->first('code') }}</p>
            @endif
            <button type="submit">Confirmar y activar</button>
        </form>

    @else
        <p class="ok">La verificación en dos pasos está activa.</p>

        <h2>Códigos de recuperación</h2>
        <p>Guárdalos en un lugar seguro (por ejemplo, un gestor de contraseñas). Cada uno sirve una sola vez si pierdes tu celular.</p>
        <ul class="codes">
            @foreach (json_decode(decrypt($u->two_factor_recovery_codes), true) as $codigo)
                <li>{{ $codigo }}</li>
            @endforeach
        </ul>
        <form method="POST" action="{{ url('/user/two-factor-recovery-codes') }}">
            @csrf
            <button type="submit" class="sec">Generar códigos nuevos</button>
        </form>

        <h2>Desactivar</h2>
        <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="sec">Desactivar verificación en dos pasos</button>
        </form>
    @endif
</div>
@endsection
