<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentRequest;
use App\Http\Resources\EquipmentResource;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;

class EquipmentController extends Controller
{
    public function index()
    {
        return EquipmentResource::collection(Equipment::orderBy('id')->get());
    }

    public function store(EquipmentRequest $request): JsonResponse
    {
        $equipment = Equipment::create($request->validated());

        return (new EquipmentResource($equipment))->response()->setStatusCode(201);
    }

    public function show(Equipment $equipment): EquipmentResource
    {
        return new EquipmentResource($equipment);
    }

    public function update(EquipmentRequest $request, Equipment $equipment): EquipmentResource
    {
        $equipment->update($request->validated());

        return new EquipmentResource($equipment);
    }

    public function destroy(Equipment $equipment): JsonResponse
    {
        $equipment->delete();

        return response()->json(['message' => 'Data berhasil dihapus.']);
    }
}
