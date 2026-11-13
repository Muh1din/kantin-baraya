<?php

namespace Tests\Feature\Owner;

use App\Models\Holiday;
use App\Models\OperationalHour;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class OperatingScheduleTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function weeklySchedule(array $overrides = []): array
    {
        $hours = [];
        foreach ([0, 1, 2, 3, 4, 5, 6] as $day) {
            $hours[$day] = [
                'is_closed' => $day === 0 ? '1' : '0',
                'open_time' => $day === 0 ? '' : '08:00',
                'close_time' => $day === 0 ? '' : '17:00',
            ];
        }

        return array_replace_recursive($hours, $overrides);
    }

    public function test_owner_can_view_and_save_all_seven_operating_days(): void
    {
        $this->actingAs($this->userWithRole('owner'))
            ->get(route('owner.operational-hours.index'))
            ->assertOk()
            ->assertSee('Jam Operasional')
            ->assertSee('Senin')
            ->assertSee('Minggu');

        $this->put(route('owner.operational-hours.update'), [
            'hours' => $this->weeklySchedule(),
        ])->assertRedirect(route('owner.operational-hours.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseCount('operational_hours', 7);
        $this->assertDatabaseHas('operational_hours', [
            'day_of_week' => 1,
            'open_time' => '08:00',
            'close_time' => '17:00',
            'is_closed' => 0,
        ]);
        $this->assertDatabaseHas('operational_hours', [
            'day_of_week' => 0,
            'open_time' => null,
            'close_time' => null,
            'is_closed' => true,
        ]);

        $this->get(route('owner.operational-hours.index'))
            ->assertOk()
            ->assertSee('value="08:00"', false)
            ->assertSee('value="17:00"', false);
    }

    public function test_open_day_requires_valid_ordered_hours_and_does_not_save_invalid_schedule(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $invalidSchedule = $this->weeklySchedule([
            2 => ['open_time' => '17:00', 'close_time' => '08:00'],
        ]);

        $this->from(route('owner.operational-hours.index'))
            ->put(route('owner.operational-hours.update'), ['hours' => $invalidSchedule])
            ->assertSessionHasErrors('hours.2.close_time');

        $this->assertSame(0, OperationalHour::query()->count());

        $invalidSchedule[2] = ['is_closed' => '0', 'open_time' => '', 'close_time' => ''];
        $this->from(route('owner.operational-hours.index'))
            ->put(route('owner.operational-hours.update'), ['hours' => $invalidSchedule])
            ->assertSessionHasErrors('hours.2.open_time');

        $this->assertSame(0, OperationalHour::query()->count());
    }

    public function test_owner_can_add_list_and_remove_holidays(): void
    {
        $this->actingAs($this->userWithRole('owner'));

        $this->get(route('owner.holidays.index'))
            ->assertOk()
            ->assertSee('Tambah hari libur')
            ->assertSee('Belum ada hari libur yang tercatat.');

        $this->post(route('owner.holidays.store'), [
            'holiday_date' => '2026-12-25',
            'name' => 'Libur Natal',
            'notes' => 'Kantin tutup sehari',
        ])->assertRedirect(route('owner.holidays.index'))
            ->assertSessionHas('status');

        $holiday = Holiday::query()->sole();
        $this->get(route('owner.holidays.index'))
            ->assertOk()
            ->assertSee('Libur Natal')
            ->assertSee('Kantin tutup sehari');

        $this->delete(route('owner.holidays.destroy', $holiday))
            ->assertRedirect(route('owner.holidays.index'))
            ->assertSessionHas('status');
        $this->assertDatabaseMissing('holidays', ['id' => $holiday->id]);
    }

    public function test_duplicate_or_invalid_holiday_is_rejected(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        Holiday::query()->create(['holiday_date' => '2026-12-25', 'name' => 'Libur Natal']);

        $this->from(route('owner.holidays.index'))
            ->post(route('owner.holidays.store'), [
                'holiday_date' => '2026-12-25',
                'name' => 'Tanggal Ganda',
            ])
            ->assertSessionHasErrors('holiday_date');

        $this->from(route('owner.holidays.index'))
            ->post(route('owner.holidays.store'), [
                'holiday_date' => 'not-a-date',
                'name' => '',
            ])
            ->assertSessionHasErrors(['holiday_date', 'name']);

        $this->assertDatabaseCount('holidays', 1);
    }

    public function test_operating_hours_and_holiday_routes_are_restricted_to_owner(): void
    {
        $cashier = $this->userWithRole('cashier');
        $holiday = Holiday::query()->create(['holiday_date' => '2026-12-25', 'name' => 'Libur Natal']);

        $this->actingAs($cashier)
            ->get(route('owner.operational-hours.index'))
            ->assertForbidden();
        $this->put(route('owner.operational-hours.update'), ['hours' => $this->weeklySchedule()])
            ->assertForbidden();
        $this->get(route('owner.holidays.index'))->assertForbidden();
        $this->post(route('owner.holidays.store'), [])->assertForbidden();
        $this->delete(route('owner.holidays.destroy', $holiday))->assertForbidden();

        Auth::logout();
        $this->get(route('owner.operational-hours.index'))->assertRedirect(route('login'));
        $this->get(route('owner.holidays.index'))->assertRedirect(route('login'));
    }
}
