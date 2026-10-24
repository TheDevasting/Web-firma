<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Caso;
use Illuminate\Http\Request;

class CasoController extends Controller
{
    /**
     * Solo abogados activos. El administrador NO ve el contenido de los casos.
     */
    private function soloAbogado(Request $request): void
    {
        $usuario = $request->user();
        abort_if($usuario->esAdmin() || ! $usuario->activo, 403);
    }

    /**
     * Lista: casos nuevos de MIS áreas (solo referencia, área y fecha) y los casos que ya tomé.
     */
    public function index(Request $request)
    {
        $this->soloAbogado($request);

        $usuario = $request->user();
        $areaIds = $usuario->areas()->pluck('areas.id');

        $nuevos = Caso::with('area')
            ->whereIn('area_id', $areaIds)
            ->where('estado', 'nueva')
            ->latest()
            ->get();

        $mios = Caso::with('area')
            ->where('tomado_por', $usuario->id)
            ->where('estado', 'tomada')
            ->latest('tomado_en')
            ->get();

        return view('casos.index', compact('nuevos', 'mios'));
    }

    /**
     * Detalle completo: solo para quien tomó el caso.
     */
    public function show(Request $request, Caso $caso)
    {
        $this->soloAbogado($request);
        abort_unless($caso->tomado_por === $request->user()->id, 403);

        Bitacora::registrar('caso.visto', $caso->id);

        return view('casos.show', compact('caso'));
    }

    /**
     * Tomar un caso nuevo de mis áreas. Si otro abogado llegó primero, no se asigna.
     */
    public function tomar(Request $request, Caso $caso)
    {
        $this->soloAbogado($request);

        $usuario = $request->user();
        abort_unless($usuario->areas()->where('areas.id', $caso->area_id)->exists(), 403);

        if (Caso::tomar($caso->id, $usuario)) {
            Bitacora::registrar('caso.tomado', $caso->id);

            return redirect()->route('casos.show', $caso)->with('status', 'Caso tomado.');
        }

        return redirect()->route('casos.index')->with('status', 'Otro abogado ya tomó ese caso.');
    }
}
