<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Glasses\Models\GlassOrder;
use App\Domain\Glasses\Models\GlassStatus;
use App\Domain\Glasses\DTOs\GlassOrderSearchDTO;
use App\Domain\Glasses\Repositories\GlassOrderRepositoryInterface;
use App\Models\Glass as EloquentGlass;
use App\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentGlassOrderRepository implements GlassOrderRepositoryInterface
{
    public function findById(int $id): ?GlassOrder
    {
        $eloquentGlass = EloquentGlass::with('patient')->find($id);

        if (!$eloquentGlass) {
            return null;
        }

        return $this->mapToDomainModel($eloquentGlass);
    }

    public function search(GlassOrderSearchDTO $searchDTO): LengthAwarePaginator
    {
        $query = EloquentGlass::with('patient');

        // Apply search filters
        if ($searchDTO->search) {
            $query->whereHas('patient', function (Builder $q) use ($searchDTO) {
                $q->where('name', 'like', "%{$searchDTO->search}%")
                    ->orWhere('phone', 'like', "%{$searchDTO->search}%");
            });
        }

        if ($searchDTO->status) {
            $query->where('status', $searchDTO->status->value);
        }

        if ($searchDTO->dateFrom) {
            $query->whereDate('created_at', '>=', $searchDTO->dateFrom->format('Y-m-d'));
        }

        if ($searchDTO->dateTo) {
            $query->whereDate('created_at', '<=', $searchDTO->dateTo->format('Y-m-d'));
        }

        $eloquentGlasses = $query->orderBy('created_at', 'desc')
            ->paginate($searchDTO->perPage, ['*'], 'page', $searchDTO->page);

        // Transform paginated results
        $transformedItems = $eloquentGlasses->getCollection()->map(function ($eloquentGlass) {
            return $this->mapToDomainModel($eloquentGlass);
        });

        $eloquentGlasses->setCollection($transformedItems);

        return $eloquentGlasses;
    }

    public function create(GlassOrder $glassOrder): GlassOrder
    {
        $eloquentGlass = EloquentGlass::create([
            'patient_id' => $glassOrder->getPatientId(),
            'lens_type' => $glassOrder->getLensType(),
            'frame_type' => $glassOrder->getFrameType(),
            'price' => $glassOrder->getPrice(),
            'status' => $glassOrder->getStatus()->value,
        ]);

        return $this->mapToDomainModel($eloquentGlass);
    }

    public function update(GlassOrder $glassOrder): GlassOrder
    {
        $eloquentGlass = EloquentGlass::findOrFail($glassOrder->getId());

        $eloquentGlass->update([
            'patient_id' => $glassOrder->getPatientId(),
            'lens_type' => $glassOrder->getLensType(),
            'frame_type' => $glassOrder->getFrameType(),
            'price' => $glassOrder->getPrice(),
            'status' => $glassOrder->getStatus()->value,
        ]);

        return $this->mapToDomainModel($eloquentGlass->fresh());
    }

    public function delete(int $id): bool
    {
        return EloquentGlass::destroy($id) > 0;
    }

    public function findByPatientId(int $patientId): array
    {
        $eloquentGlasses = EloquentGlass::with('patient')
            ->where('patient_id', $patientId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $eloquentGlasses->map(function ($eloquentGlass) {
            return $this->mapToDomainModel($eloquentGlass);
        })->toArray();
    }

    public function findByStatus(string $status): array
    {
        $eloquentGlasses = EloquentGlass::with('patient')
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return $eloquentGlasses->map(function ($eloquentGlass) {
            return $this->mapToDomainModel($eloquentGlass);
        })->toArray();
    }

    public function getCountByStatus(): array
    {
        return EloquentGlass::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function patientExists(int $patientId): bool
    {
        return Patient::where('id', $patientId)->exists();
    }

    private function mapToDomainModel(EloquentGlass $eloquentGlass): GlassOrder
    {
        return new GlassOrder(
            id: $eloquentGlass->id,
            patientId: $eloquentGlass->patient_id,
            lensType: $eloquentGlass->lens_type,
            frameType: $eloquentGlass->frame_type,
            price: (float) $eloquentGlass->price,
            status: GlassStatus::fromString($eloquentGlass->status),
            createdAt: $eloquentGlass->created_at,
            updatedAt: $eloquentGlass->updated_at
        );
    }
}
