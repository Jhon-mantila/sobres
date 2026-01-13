<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RegistroImagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegistroImagenController extends Controller
{
    public function indexBySobre($sobreId)
    {
        $imagenes = RegistroImagen::where('sobre_plantilla_id', $sobreId)
            ->orderBy('orden')
            ->get();

        return response()->json($imagenes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sobre_plantilla_id' => 'required|string|exists:sobre_plantillas,id',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $guardadas = [];

        $ultimoOrden = RegistroImagen::where('sobre_plantilla_id', $request->sobre_plantilla_id)
            ->max('orden') ?? 0;

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $folder = 'images/' . $request->sobre_plantilla_id;
                $path = $file->store($folder, 'public');

                $nombreSinExtension = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                $orden = $ultimoOrden + $index + 1;

                $img = RegistroImagen::create([
                    'id' => (string) Str::uuid(),
                    'sobre_plantilla_id' => $request->sobre_plantilla_id,
                    'imagen' => $path,
                    'title' => $nombreSinExtension,
                    'tipo' => $file->getClientMimeType(),
                    'orden' => $orden,
                ]);

                $guardadas[] = $img;
            }
        }

        return response()->json([
            'message' => 'Imágenes subidas con éxito',
            'images' => $guardadas
        ], 201);
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:registro_imagens,id',
            'orders.*.order' => 'required|integer|min:1',
        ]);

        foreach ($request->orders as $o) {
            RegistroImagen::where('id', $o['id'])->update(['orden' => $o['order']]);
        }

        return response()->json(['message' => 'Orden actualizado correctamente']);
    }

    public function destroy($id)
    {
        $registro = RegistroImagen::findOrFail($id);

        $rutaImagen = public_path("storage/{$registro->imagen}");
        if (file_exists($rutaImagen)) {
            unlink($rutaImagen);
        } else {
            Log::warning("⚠️ Archivo no encontrado: {$rutaImagen}");
        }

        $registro->delete();

        return response()->json(['message' => 'Imagen eliminada correctamente']);
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|exists:registro_imagens,id',
        ]);

        $deleted = [];

        foreach ($request->ids as $id) {
            $registro = RegistroImagen::findOrFail($id);

            $rutaImagen = public_path("storage/{$registro->imagen}");
            if (file_exists($rutaImagen)) {
                unlink($rutaImagen);
            }

            $registro->delete();
            $deleted[] = $id;
        }

        return response()->json([
            'message' => 'Imágenes eliminadas correctamente',
            'deleted_ids' => $deleted
        ]);
    }
}
