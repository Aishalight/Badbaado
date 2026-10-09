<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\DoctorResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoctorController extends Controller
{
    /**
     * Search the independent doctors a referral may be addressed to.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'specialty_id' => ['nullable', 'integer', 'exists:specialties,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $doctors = User::query()
            ->independentDoctors()
            ->when(
                $filters['q'] ?? $filters['name'] ?? null,
                fn ($query, string $term) => $query->where(function ($query) use ($term): void {
                    $term = $this->searchTerm($term);
                    $query->where('name', 'like', '%'.$term.'%')
                        ->orWhereHas('specialty', fn ($specialty) => $specialty->where('name', 'like', '%'.$term.'%'));
                }),
            )
            ->when(
                $filters['specialty_id'] ?? null,
                fn ($query, string $specialtyId) => $query->where('specialty_id', $specialtyId),
            )
            ->with(['specialty:id,name,slug', 'hospital:id,name,kind'])
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 25))
            ->withQueryString();

        return DoctorResource::collection($doctors);
    }

    /**
     * Strip LIKE wildcards so a search term matches literally.
     *
     * The escape character differs between MySQL and SQLite, so removing the
     * wildcards is the portable option.
     */
    private function searchTerm(string $term): string
    {
        return str_replace(['\\', '%', '_'], ['', '', ''], trim($term));
    }
}
