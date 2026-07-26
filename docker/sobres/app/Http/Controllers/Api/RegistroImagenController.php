<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RegistroImagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            'images.*' => 'required|image|mimes:jpeg,png,jpg,jfif,gif,webp|max:6096',
        ]);

        $guardadas = [];

        $ultimoOrden = RegistroImagen::where('sobre_plantilla_id', $request->sobre_plantilla_id)
            ->max('orden') ?? 0;

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $folder = 'images/' . $request->sobre_plantilla_id;
                $path = $this->storeImageFile($file, $folder);

                $nombreSinExtension = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                $orden = $ultimoOrden + $index + 1;

                $img = RegistroImagen::create([
                    'id' => (string) Str::uuid(),
                    'sobre_plantilla_id' => $request->sobre_plantilla_id,
                    'imagen' => $path,
                    'title' => $nombreSinExtension,
                    'tipo' => $this->mimeFromStoredPath($path),
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

    public function update(Request $request, $id)
    {
        $imagen = \App\Models\RegistroImagen::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,jfif,gif,webp|max:6096',
        ]);

        // Siempre actualiza título
        $imagen->title = $validated['title'];

        // Si viene imagen nueva, reemplaza
        if ($request->hasFile('image')) {
            // eliminar anterior
            $oldPath = public_path("storage/{$imagen->imagen}");
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }

            $folder = 'images/' . $imagen->sobre_plantilla_id;
            $file = $request->file('image');
            $path = $this->storeImageFile($file, $folder);

            $imagen->imagen = $path;
            $imagen->tipo = $this->mimeFromStoredPath($path);
        }

        $imagen->save();

        return response()->json(['message' => 'Imagen actualizada con éxito']);
    }

    /**
     * Guarda la imagen:
     * - .jfif/.jpeg → .jpg
     * - .webp → convierte a .jpg (TCPDF no embebe WebP de forma fiable)
     */
    private function storeImageFile($file, string $folder): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $mime = strtolower((string) ($file->getMimeType() ?: $file->getClientMimeType() ?: ''));

        if ($extension === 'webp' || $mime === 'image/webp') {
            return $this->storeWebpAsJpeg($file, $folder);
        }

        if ($extension === 'jfif' || $extension === 'jpeg') {
            $extension = 'jpg';
        }

        $filename = Str::random(40) . '.' . $extension;

        return $file->storeAs($folder, $filename, 'public');
    }

    private function storeWebpAsJpeg($file, string $folder): string
    {
        if (!function_exists('imagecreatefromwebp') || !function_exists('imagejpeg')) {
            throw ValidationException::withMessages([
                'images' => 'El servidor no soporta conversión WebP. Reconstruye la imagen Docker con GD+WebP.',
            ]);
        }

        $src = @imagecreatefromwebp($file->getRealPath());
        if ($src === false) {
            throw ValidationException::withMessages([
                'images' => 'No se pudo leer la imagen WebP.',
            ]);
        }

        $width = imagesx($src);
        $height = imagesy($src);
        $dst = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);
        imagecopy($dst, $src, 0, 0, 0, 0, $width, $height);

        $filename = Str::random(40) . '.jpg';
        $relative = trim($folder, '/') . '/' . $filename;
        $absolute = Storage::disk('public')->path($relative);

        $directory = dirname($absolute);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            imagedestroy($src);
            imagedestroy($dst);
            throw ValidationException::withMessages([
                'images' => 'No se pudo crear el directorio de imágenes.',
            ]);
        }

        $ok = imagejpeg($dst, $absolute, 90);
        imagedestroy($src);
        imagedestroy($dst);

        if (!$ok) {
            throw ValidationException::withMessages([
                'images' => 'No se pudo convertir la imagen WebP a JPEG.',
            ]);
        }

        return $relative;
    }

    private function mimeFromStoredPath(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
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
