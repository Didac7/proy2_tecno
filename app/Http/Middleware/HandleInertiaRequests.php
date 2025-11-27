<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use App\Models\VisitaPagina;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        // Obtener menú dinámico según rol del usuario
        $menuItems = [];
        if ($user) {
            $menuItems = Menu::with('hijos')
                ->activo()
                ->whereNull('padre_id')
                ->orderBy('orden')
                ->get()
                ->filter(function ($menu) use ($user) {
                    if (!$menu->roles) return true;
                    $roles = is_array($menu->roles) ? $menu->roles : json_decode($menu->roles, true);
                    return in_array($user->tipo_usuario, $roles);
                })
                ->map(function ($menu) use ($user) {
                    // Filtrar hijos también por rol
                    if ($menu->hijos) {
                        $menu->setRelation('hijos', $menu->hijos->filter(function ($hijo) use ($user) {
                            if (!$hijo->roles) return true;
                            $roles = is_array($hijo->roles) ? $hijo->roles : json_decode($hijo->roles, true);
                            return in_array($user->tipo_usuario, $roles);
                        })->values());
                    }
                    return $menu;
                })
                ->values();
        }

        // Obtener contador de visitas de la página actual
        $visitCount = 0;
        $pagina = $request->path();
        $visita = VisitaPagina::where('pagina', $pagina)->first();
        if ($visita) {
            $visitCount = $visita->contador;
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id_usuario' => $user->id_usuario,
                    'nombre' => $user->nombre,
                    'apellido' => $user->apellido,
                    'email' => $user->email,
                    'tipo_usuario' => $user->tipo_usuario,
                ] : null,
            ],
            'menuItems' => $menuItems,
            'visitCount' => $visitCount,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }
}
