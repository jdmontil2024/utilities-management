<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class BuildingsSimpleTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_buildings_table_exists()
    {
        $this->assertTrue(
            DB::getSchemaBuilder()->hasTable('buildings'),
            'Buildings table does not exist'
        );
        
        echo "\n✅ Buildings table exists\n";
    }
    
    public function test_can_create_building()
    {
        $id = DB::table('buildings')->insertGetId([
            'name' => 'Test Building',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '12345',
            'country' => 'USA',
            'total_floors' => 5,
            'total_units' => 20,
            'year_built' => 2020,
            'building_type' => 'commercial',
            'has_elevator' => true,
            'has_parking' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $building = DB::table('buildings')->find($id);
        
        $this->assertNotNull($building);
        $this->assertEquals('Test Building', $building->name);
        $this->assertEquals(20, $building->total_units);
        
        echo "\n✅ Created building: {$building->name} (ID: {$building->id})\n";
    }
    
    public function test_can_update_building()
    {
        // Create
        $id = DB::table('buildings')->insertGetId([
            'name' => 'Original Name',
            'address' => 'Test Address',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '12345',
            'country' => 'USA',
            'total_floors' => 3,
            'total_units' => 10,
            'year_built' => 2010,
            'building_type' => 'residential',
            'has_elevator' => false,
            'has_parking' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Update
        DB::table('buildings')
            ->where('id', $id)
            ->update([
                'name' => 'Updated Name',
                'total_units' => 25,
                'updated_at' => now()
            ]);
        
        $building = DB::table('buildings')->find($id);
        
        $this->assertEquals('Updated Name', $building->name);
        $this->assertEquals(25, $building->total_units);
        
        echo "\n✅ Updated building: {$building->name} (Units: {$building->total_units})\n";
    }
    
    public function test_can_delete_building()
    {
        // Create
        $id = DB::table('buildings')->insertGetId([
            'name' => 'To Delete',
            'address' => 'Delete Address',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '12345',
            'country' => 'USA',
            'total_floors' => 2,
            'total_units' => 5,
            'year_built' => 2000,
            'building_type' => 'residential',
            'has_elevator' => false,
            'has_parking' => false,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Verify exists
        $this->assertNotNull(DB::table('buildings')->find($id));
        
        // Delete
        DB::table('buildings')->delete($id);
        
        // Verify deleted
        $this->assertNull(DB::table('buildings')->find($id));
        
        echo "\n✅ Building deleted successfully\n";
    }
    
    public function test_can_list_buildings()
    {
        // Create multiple buildings
        DB::table('buildings')->insert([
            [
                'name' => 'Building 1',
                'address' => 'Addr1',
                'city' => 'City1',
                'state' => 'ST',
                'zip_code' => '11111',
                'country' => 'USA',
                'total_floors' => 1,
                'total_units' => 10,
                'year_built' => 2000,
                'building_type' => 'residential',
                'has_elevator' => false,
                'has_parking' => false,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Building 2',
                'address' => 'Addr2',
                'city' => 'City2',
                'state' => 'ST',
                'zip_code' => '22222',
                'country' => 'USA',
                'total_floors' => 2,
                'total_units' => 20,
                'year_built' => 2005,
                'building_type' => 'commercial',
                'has_elevator' => true,
                'has_parking' => true,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Building 3',
                'address' => 'Addr3',
                'city' => 'City3',
                'state' => 'ST',
                'zip_code' => '33333',
                'country' => 'USA',
                'total_floors' => 3,
                'total_units' => 30,
                'year_built' => 2010,
                'building_type' => 'industrial',
                'has_elevator' => true,
                'has_parking' => true,
                'status' => 'maintenance',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        
        $buildings = DB::table('buildings')->get();
        
        $this->assertCount(3, $buildings);
        
        echo "\n✅ Found " . $buildings->count() . " buildings:\n";
        foreach ($buildings as $building) {
            echo "   - {$building->name} | Units: {$building->total_units} | Status: {$building->status}\n";
        }
    }
}
