<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EquipmentTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'name' => 'Oscilloscope',
        'code' => 'ALT-9001',
        'category' => 'Elektronika',
        'condition' => 'baik',
        'quantity' => 2,
    ];

    public function test_requests_without_token_return_401(): void
    {
        $this->getJson('/api/equipment')->assertUnauthorized();
        $this->postJson('/api/equipment', $this->payload)->assertUnauthorized();
    }

    public function test_index_lists_equipment(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Equipment::factory()->count(3)->create();

        $this->getJson('/api/equipment')->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_store_creates_equipment_with_201(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/equipment', $this->payload)
            ->assertCreated()
            ->assertJsonPath('data.code', 'ALT-9001');

        $this->assertDatabaseHas('equipment', ['code' => 'ALT-9001']);
    }

    public function test_store_with_invalid_data_returns_422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/equipment', ['name' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'code', 'category', 'condition', 'quantity']);
    }

    public function test_show_update_and_destroy(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $equipment = Equipment::factory()->create();

        $this->getJson("/api/equipment/{$equipment->id}")->assertOk()->assertJsonPath('data.id', $equipment->id);

        $this->putJson("/api/equipment/{$equipment->id}", [...$this->payload, 'name' => 'Diubah'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Diubah');

        $this->deleteJson("/api/equipment/{$equipment->id}")->assertOk();
        $this->assertDatabaseMissing('equipment', ['id' => $equipment->id]);
    }
}
