@extends('layout')
@section('titulo', 'Administración')
@section('contenido')
@include('barra')
<div class="caja ancho" style="max-width:1000px">
    <h1>Administración</h1>
    <p class="sub">Cuentas, áreas y bitácora. Aquí no se ven los casos.</p>

    @if (session('status'))<p class="ok">{{ session('status') }}</p>@endif

    @if (session('clave_temporal'))
        <div class="ok">
            <strong>Clave temporal de {{ session('clave_temporal.correo') }}:</strong>
            <code>{{ session('clave_temporal.clave') }}</code><br>
            Se muestra solo esta vez. Entrégala en persona o por llamada (no por correo ni chat). Vence en 48 horas y deberá cambiarla al entrar.
        </div>
    @endif

    <h2>Crear abogado</h2>
    <form method="POST" action="{{ route('admin.crear') }}">
        @csrf
        <label for="name">Nombre</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required>
        @error('name')<p class="error">{{ $message }}</p>@enderror

        <label for="email">Correo de trabajo</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>
        @error('email')<p class="error">{{ $message }}</p>@enderror

        <label>Áreas que atenderá</label>
        @foreach ($areas as $area)
            <div><label style="font-weight:400;display:inline"><input type="checkbox" name="areas[]" value="{{ $area->id }}"> {{ $area->nombre }}</label></div>
        @endforeach
        @error('areas')<p class="error">{{ $message }}</p>@enderror

        <button type="submit">Crear cuenta con clave temporal</button>
    </form>

    <h2>Cuentas</h2>
    <table>
        <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Áreas</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        @foreach ($usuarios as $u)
            <tr>
                <td>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td>{{ $u->rol }}</td>
                <td>{{ $u->areas->pluck('nombre')->join(', ') ?: '—' }}</td>
                <td>{{ $u->activo ? 'Activa' : 'Desactivada' }}{{ $u->debe_cambiar_clave ? ' · clave temporal' : '' }}</td>
                <td>
                    @if ($u->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.resetear', $u) }}">@csrf<button class="peq sec" type="submit">Restablecer clave</button></form>
                        <form method="POST" action="{{ route('admin.quitar2fa', $u) }}">@csrf<button class="peq sec" type="submit">Quitar 2FA</button></form>
                        <form method="POST" action="{{ route('admin.activo', $u) }}">@csrf<button class="peq sec" type="submit">{{ $u->activo ? 'Desactivar' : 'Activar' }}</button></form>
                    @else
                        (tu cuenta)
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2>Bitácora (últimos 40 eventos)</h2>
    <table>
        <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Detalle</th><th>IP</th></tr></thead>
        <tbody>
        @foreach ($bitacora as $b)
            <tr>
                <td>{{ $b->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $b->usuario?->email ?? '—' }}</td>
                <td>{{ $b->accion }}</td>
                <td>{{ $b->detalle }}</td>
                <td>{{ $b->ip }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
