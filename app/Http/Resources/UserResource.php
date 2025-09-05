<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class UserResource
 * 
 * API resource for transforming User model data into a consistent JSON format.
 * Provides structured output for user data while protecting sensitive information.
 * 
 * @package App\Http\Resources
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => [
                'name' => $this->role,
                'display_name' => $this->getRoleDisplayName(),
                'permissions' => $this->getPermissions(),
            ],
            'account' => [
                'email_verified_at' => $this->email_verified_at?->toISOString(),
                'is_verified' => !is_null($this->email_verified_at),
                'created_at' => $this->created_at->toISOString(),
                'updated_at' => $this->updated_at->toISOString(),
                'member_since' => $this->created_at->diffForHumans(),
            ],
            'links' => [
                'profile' => route('api.v1.auth.profile'),
                'update_profile' => route('api.v1.auth.profile'),
            ],
        ];
    }

    /**
     * Get user permissions based on role.
     *
     * @return array<string>
     */
    private function getPermissions(): array
    {
        return match ($this->role) {
            'admin' => [
                'manage_users',
                'manage_bnbs',
                'view_statistics',
                'manage_roles',
                'delete_any_bnb',
                'access_admin_panel',
            ],
            'user' => [
                'create_bnb',
                'update_own_bnb',
                'view_bnbs',
                'manage_profile',
            ],
            default => ['view_bnbs'],
        };
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => 'v1',
                'timestamp' => now()->toISOString(),
                'resource_type' => 'user',
            ],
        ];
    }
}
