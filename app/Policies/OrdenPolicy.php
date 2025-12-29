<?php

namespace App\Policies;

use App\Models\Orden;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrdenPolicy
{
    /**
     * Permite que el rol 'administrador' pase todas las validaciones automáticamente.
     * Esta función se ejecuta antes que cualquier otra en la policy.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('administrador')) {
            return true;
        }

        return null; // Dejar que la policy decida para otros roles
    }

    /**
     * Determina si el usuario puede ver la lista de órdenes.
     * Se deja abierto para que todos vean la página, pero el contenido
     * se filtra en el Resource de Filament.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede ver una orden específica.
     */
    public function view(User $user, Orden $orden): bool
    {
        // El operador puede ver cualquier orden.
        if ($user->hasRole('operador')) {
            return true;
        }

        // El técnico solo puede ver sus órdenes asignadas.
        if ($user->hasRole('tecnico')) {
            return $user->id === $orden->technician_id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede crear órdenes.
     */
    public function create(User $user): bool
    {
        // Solo administradores y operadores pueden crear.
        return $user->hasRole('operador');
    }

    /**
     * Determina si el usuario puede actualizar una orden.
     */
    public function update(User $user, Orden $orden): bool
    {
        // El operador puede actualizar cualquier orden.
        if ($user->hasRole('operador')) {
            return true;
        }
        
        // El técnico solo puede actualizar sus órdenes asignadas.
        if ($user->hasRole('tecnico')) {
            return $user->id === $orden->technician_id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede eliminar una orden.
     */
    public function delete(User $user, Orden $orden): bool
    {
        // Solo el operador puede eliminar (además del admin).
        return $user->hasRole('operador');
    }

    /**
     * Determina si el usuario puede restaurar una orden eliminada.
     */
    public function restore(User $user, Orden $orden): bool
    {
        // Generalmente solo roles de alta jerarquía.
        return $user->hasRole('operador');
    }

    /**
     * Determina si el usuario puede eliminar permanentemente una orden.
     */
    public function forceDelete(User $user, Orden $orden): bool
    {
        // Acción muy restrictiva, solo para el admin (manejado por el `before`).
        return false;
    }
}