<?php

namespace App\Actions\Families;

use App\Models\AuditLog;
use App\Models\Family;
use App\Models\Person;
use Illuminate\Support\Facades\DB;

class AddFamilyMemberAction
{
    /**
     * Add a person as a member to a family.
     *
     * @param  array<string, mixed>  $pivotData
     */
    public function execute(Family $family, Person $person, array $pivotData = []): void
    {
        DB::transaction(function () use ($family, $person, $pivotData) {
            $family->members()->attach($person->id, array_merge($pivotData, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'member_added',
                'auditable_type' => Family::class,
                'auditable_id' => $family->id,
                'comment' => "Added person {$person->id} to family",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }

    /**
     * Remove a person from a family.
     */
    public function remove(Family $family, Person $person): void
    {
        DB::transaction(function () use ($family, $person) {
            $family->members()->detach($person->id);

            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'member_removed',
                'auditable_type' => Family::class,
                'auditable_id' => $family->id,
                'comment' => "Removed person {$person->id} from family",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }
}
