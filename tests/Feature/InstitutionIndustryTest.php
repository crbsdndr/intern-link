<?php

namespace Tests\Feature;

use App\Models\Institution;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class InstitutionIndustryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        \DB::statement('DROP TABLE IF EXISTS institutions CASCADE');

        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('website')->nullable();
            $table->string('industry');
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('institutions');
        parent::tearDown();
    }

    public function test_industry_is_saved_and_updated(): void
    {
        $institution = Institution::create([
            'name' => 'Acme Inc',
            'industry' => 'Technology',
        ]);

        $this->assertDatabaseHas('institutions', [
            'id' => $institution->id,
            'industry' => 'Technology',
        ]);

        $institution->update(['industry' => 'Finance']);

        $this->assertDatabaseHas('institutions', [
            'id' => $institution->id,
            'industry' => 'Finance',
        ]);
    }
}
