<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SobrePlantilla;
use Inertia\Inertia;

class SobrePlantillaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener el término de búsqueda
        $search = $request->query('search');

        // Consulta base 
        $query = SobrePlantilla::query();
        // 👇 agrega esto
        $query->withCount('imagenes');
        // Aplicar búsqueda si hay un término
        if ($search) {
            $query->where('nombre_sobre', 'LIKE', "%{$search}%");
        }

        // Ordenar los resultados en orden descendente por la columna 'created_at'
        $query->orderBy('created_at', 'desc');

        // Paginar los resultados y conservar el parámetro de búsqueda
        $sobres_plantillas = $query->paginate(10)->appends(['search' => $search]);

       
        return Inertia::render('SobresPlantilla/Index', [
            'sobres_plantillas' => $sobres_plantillas,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('SobresPlantilla/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_sobre' => ['required', 'string', 'max:255', 'unique:sobre_plantillas,nombre_sobre'],
        ], [
            'nombre_sobre.required' => 'El nombre del sobre es obligatorio.',
            'nombre_sobre.unique' => 'Ya existe una plantilla con ese nombre.',
        ]);

        $sobre = SobrePlantilla::create([
            'nombre_sobre' => $validated['nombre_sobre'],
        ]);

        return redirect()
            ->route('sobres-plantilla.index')
            ->with('success', 'Plantilla creada correctamente.');
    
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
        $sobre = SobrePlantilla::with('imagenes')
            ->findOrFail($id);
        
        return Inertia::render('SobresPlantilla/Show', [
            'sobre' => $sobre,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $sobre = SobrePlantilla::findOrFail($id);

        return Inertia::render('SobresPlantilla/Edit', [
            'sobre' => $sobre,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
    $sobre = SobrePlantilla::findOrFail($id);

        $validated = $request->validate([
            'nombre_sobre' => [
                'required',
                'string',
                'max:255',
                // unique pero permitiendo el mismo registro actual
                'unique:sobre_plantillas,nombre_sobre,' . $sobre->id . ',id',
            ],
        ], [
            'nombre_sobre.required' => 'El nombre del sobre es obligatorio.',
            'nombre_sobre.unique' => 'Ya existe una plantilla con ese nombre.',
        ]);

        $sobre->update([
            'nombre_sobre' => $validated['nombre_sobre'],
        ]);

        // updated_at se actualiza solo por timestamps()
        return redirect()
            ->route('sobres-plantilla.show', $sobre->id)
            ->with('success', 'Plantilla actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
