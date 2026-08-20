<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->when($this->isCurrentUser($request), $this->email),
            'avatar' => $this->avatar,
            'role' => $this->role,
            'country' => $this->country,
            'is_premium' => $this->is_premium,
            'created_at' => $this->created_at,
        ];
    }

    private function isCurrentUser(Request $request): bool
    {
        return $request->user() && $request->user()->id === $this->id;
    }
}
