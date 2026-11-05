<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentRequest;
use App\Http\Resources\EquipmentResource;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EquipmentController extends Controller
{
    public function index()
    {
        return EquipmentResource::collection(Equipment::orderBy('id')->get());
    }

    public function store(EquipmentRequest $request): JsonResponse
    {
        $equipment = Equipment::create($request->validated());

        $this->audit('equipment.created', 'menyimpan data alat baru', $request->user()->id, $equipment);

        return (new EquipmentResource($equipment))->response()->setStatusCode(201);
    }

    public function show(Equipment $equipment): EquipmentResource
    {
        return new EquipmentResource($equipment);
    }

    public function update(EquipmentRequest $request, Equipment $equipment): EquipmentResource
    {
        $equipment->update($request->validated());

        $this->audit('equipment.updated', 'memperbarui data alat', $request->user()->id, $equipment);

        return new EquipmentResource($equipment);
    }

    public function destroy(Request $request, Equipment $equipment): JsonResponse
    {
        $equipment->delete();

        $this->audit('equipment.deleted', 'menghapus data alat', $request->user()->id, $equipment);

        return response()->json(['message' => 'Data berhasil dihapus.']);
    }

    private function audit(string $event, string $action, int $userId, Equipment $equipment): void
    {
        Log::info(ucfirst($action), [
            'event' => $event,
            'user_id' => $userId,
            'action' => $action,
            'equipment_id' => $equipment->id,
            'equipment_code' => $equipment->code,
        ]);
    }
}
