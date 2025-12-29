<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Permite que el rol 'administrador' pase todas las validaciones automáticamente.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('administrador')) {
            return true;
        }

        return null; // Dejar que la policy decida para otros roles
    }

    /**
     * Determina si el usuario puede ver la lista de usuarios.
     * (Solo el admin, gestionado por 'before').
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determina si el usuario puede ver un perfil de usuario específico.
     * (Solo el admin, gestionado por 'before').
     */
    public function view(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determina si el usuario puede crear nuevos usuarios.
     * (Solo el admin, gestionado por 'before').
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determina si el usuario puede actualizar un perfil de usuario.
     * (Solo el admin, gestionado por 'before').
     */
    public function update(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determina si el usuario puede eliminar un usuario.
     * Se añade una capa extra para que un admin no pueda eliminarse a sí mismo.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->hasRole('administrador')) {
            return $user->id !== $model->id; // Un admin no puede borrarse a sí mismo
        }

        return false;
    }

    /**
     * Determina si el usuario puede restaurar un usuario.
     * (Solo el admin, gestionado por 'before').
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determina si el usuario puede eliminar permanentemente un usuario.
     * (Solo el admin, gestionado por 'before').
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}