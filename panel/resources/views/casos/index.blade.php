@extends('layout')
@section('titulo', 'Casos')
@section('contenido')
@include('barra')
<div class="caja ancho">
    <h1>Casos</h1>
    <p class="sub">Hola, {{ auth()->user()->name }}.</p>

    @if (session('status'))
        <p class="ok">{{ session('status') }}</p>
    @endif

    <h2>Casos nuevos de mis áreas</h2>
    @if ($nuevos->isEmpty())
        <p>No hay casos nuevos por ahora.</p>
    @else
        <table>
            <thead><tr><th>Referencia</th><th>Área</th><th>Fecha</th><th></th></tr></thead>
            <tbody>
            @foreach ($nuevos as $caso)
                <tr>
                    <td><code>{{ $caso->referencia }}</code></td>
                    <td>{{ $caso->area->nombre }}</td>
                    <td>{{ $caso->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <form method="POST" action="{{ route('casos.tomar', $caso) }}">
                            @csrf
                            <button type="submit" class="peq">Tomar caso</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <h2>Mis casos</h2>
    @if ($mios->isEmpty())
        <p>Todavía no has tomado ningún caso.</p>
    @else
        <table>
            <thead><tr><th>Referencia</th><th>Área</th><th>Tomado</th><th></th></tr></thead>
            <tbody>
            @foreach ($mios as $caso)
                <tr>
                    <td><code>{{ $caso->referencia }}</code></td>
                    <td>{{ $caso->area->nombre }}</td>
                    <td>{{ $caso->tomado_en?->format('d/m/Y H:i') }}</td>
                    <td><a href="{{ route('casos.show', $caso) }}">Ver caso</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
