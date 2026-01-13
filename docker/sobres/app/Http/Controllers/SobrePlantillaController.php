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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
