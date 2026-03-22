<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\BuildingPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class BuildingPhotoController extends Controller
{
    public function upload(Request $request, Building $building)
    {
        $request->validate([
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
            'category' => 'required|string|max:50',
            'description' => 'nullable|string|max:255'
        ]);
        
        $uploadedPhotos = [];
        
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                // Generate unique filename
                $filename = 'photo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Store file in storage/app/public/building_photos
                $path = $file->storeAs('building_photos', $filename, 'public');
                
                // Create photo record
                $photo = BuildingPhoto::create([
                    'building_id' => $building->id,
                    'path' => $path,
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'category' => $request->category,
                    'description' => $request->description,
                    'uploaded_by' => Auth::user()->name,
                    'is_primary' => false
                ]);
                
                $uploadedPhotos[] = $photo;
            }
            
            // If this is the first photo, set it as primary
            if ($building->photos()->count() === count($uploadedPhotos)) {
                $uploadedPhotos[0]->update(['is_primary' => true]);
            }
            
            return response()->json([
                'success' => true,
                'message' => count($uploadedPhotos) . ' photo(s) uploaded successfully!',
                'photos' => $uploadedPhotos
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'No photos were uploaded.'
        ], 400);
    }
    
    public function setPrimary(Building $building, BuildingPhoto $photo)
    {
        // Verify photo belongs to building
        if ($photo->building_id !== $building->id) {
            return response()->json([
                'success' => false,
                'message' => 'Photo not found for this building.'
            ], 404);
        }
        
        // Update all photos to not primary
        $building->photos()->update(['is_primary' => false]);
        
        // Set this photo as primary
        $photo->update(['is_primary' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Primary photo updated successfully.'
        ]);
    }
    
    public function destroy(Building $building, BuildingPhoto $photo)
    {
        // Verify photo belongs to building
        if ($photo->building_id !== $building->id) {
            return response()->json([
                'success' => false,
                'message' => 'Photo not found for this building.'
            ], 404);
        }
        
        // Delete file from storage
        Storage::disk('public')->delete($photo->path);
        
        // Delete record from database
        $photo->delete();
        
        // If this was the primary photo and there are other photos, set a new primary
        if ($photo->is_primary && $building->photos()->count() > 0) {
            $building->photos()->first()->update(['is_primary' => true]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Photo deleted successfully.'
        ]);
    }
    
    public function index(Building $building)
    {
        $photos = $building->photos()->latest()->get();
        $categoryLabels = [
            'exterior' => 'Exterior',
            'lobby' => 'Lobby',
            'amenities' => 'Amenities',
            'unit_sample' => 'Unit Sample',
            'floor_plans' => 'Floor Plans',
            'other' => 'Other'
        ];
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'photos' => $photos
            ]);
        }
        
        // Return HTML for AJAX reload
        return view('partials.building-photos', compact('photos', 'categoryLabels', 'building'));
    }
}