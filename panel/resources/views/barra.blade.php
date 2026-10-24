<div class="barra">
    <div>
        @if (auth()->user()->esAdmin())
            <a href="{{ route('admin.index') }}">Administración</a>
        @else
            <a href="{{ route('casos.index') }}">Casos</a>
        @endif
        <a href="{{ route('clave.cambiar') }}">Contraseña</a>
        <a href="{{ route('seguridad') }}">Seguridad</a>
    </div>
    <form method="POST" action="{{ url('/logout') }}">
        @csrf
        <button type="submit" class="sec">Cerrar sesión</button>
    </form>
</div>
