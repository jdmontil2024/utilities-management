<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\BuildingPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BuildingPhotoController extends Controller
{
    /**
     * Upload photos for a building
     */
    public function upload(Request $request, Building $building)
    {
        try {
            $request->validate([
                'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
                'category' => 'required|string|max:50',
                'description' => 'nullable|string|max:255'
            ]);
            
            $uploadedPhotos = [];
            
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $file) {
                    $filename = 'photo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('building_photos', $filename, 'public');
                    
                    $photo = BuildingPhoto::create([
                        'building_id' => $building->id,
                        'path' => $path,
                        'filename' => $filename,
                        'original_name' => $file->getClientOriginalName(),
                        'category' => $request->category,
                        'description' => $request->description,
                        'uploaded_by' => Auth::user()->name ?? 'System',
                        'is_primary' => false
                    ]);
                    
                    // Add URL for JSON response (DO NOT save to database)
                    $photoData = $photo->toArray();
                    $photoData['url'] = asset('storage/' . $path);
                    $uploadedPhotos[] = (object)$photoData;
                }
                
                if ($building->photos()->count() === count($uploadedPhotos)) {
                    $building->photos()->first()->update(['is_primary' => true]);
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
            
        } catch (\Exception $e) {
            Log::error('Photo upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Set a photo as the primary photo for the building
     */
    public function setPrimary(Building $building, BuildingPhoto $photo)
    {
        try {
            if ($photo->building_id !== $building->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Photo not found for this building.'
                ], 404);
            }
            
            $building->photos()->update(['is_primary' => false]);
            $photo->update(['is_primary' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Primary photo updated successfully.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Set primary error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to set primary photo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete a photo
     */
    public function destroy(Building $building, BuildingPhoto $photo)
    {
        try {
            if ($photo->building_id !== $building->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Photo not found for this building.'
                ], 404);
            }
            
            if (Storage::disk('public')->exists($photo->path)) {
                Storage::disk('public')->delete($photo->path);
            }
            
            $wasPrimary = $photo->is_primary;
            $photo->delete();
            
            if ($wasPrimary && $building->photos()->count() > 0) {
                $building->photos()->first()->update(['is_primary' => true]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Photo deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Delete photo error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete photo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get all photos for a building
     */
    public function index(Building $building)
    {
        $photos = $building->photos()->latest()->get();
        
        // Add URL for JSON response
        $photoData = [];
        foreach ($photos as $photo) {
            $data = $photo->toArray();
            $data['url'] = asset('storage/' . $photo->path);
            $photoData[] = (object)$data;
        }
        
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
                'photos' => $photoData,
                'categoryLabels' => $categoryLabels
            ]);
        }
        
        return view('partials.building-photos', compact('photos', 'categoryLabels', 'building'));
    }
}