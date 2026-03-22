<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Vendor::query();
        
        if ($request->has('is_approved')) {
            $query->where('is_approved', $request->boolean('is_approved'));
        }
        
        if ($request->has('service_type')) {
            $query->where('service_type', $request->service_type);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $vendors = $query->paginate(20);
        return response()->json($vendors);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:200',
            'contact_person' => 'nullable|string|max:200',
            'email' => 'required|email|unique:vendors,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:100',
            'service_type' => 'required|string|max:100',
            'hourly_rate' => 'nullable|numeric|min:0',
            'callout_fee' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'is_approved' => 'boolean',
            'rating' => 'nullable|numeric|min:0|max:5',
        ]);

        $vendor = Vendor::create($validated);
        return response()->json($vendor, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendor $vendor)
    {
        $vendor->load(['maintenanceRequests', 'repairs']);
        return response()->json($vendor);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'company_name' => 'sometimes|required|string|max:200',
            'contact_person' => 'nullable|string|max:200',
            'email' => 'sometimes|required|email|unique:vendors,email,' . $vendor->id,
            'phone' => 'sometimes|required|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:100',
            'service_type' => 'sometimes|required|string|max:100',
            'hourly_rate' => 'nullable|numeric|min:0',
            'callout_fee' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'is_approved' => 'boolean',
            'rating' => 'nullable|numeric|min:0|max:5',
        ]);

        $vendor->update($validated);
        return response()->json($vendor);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore soft deleted vendor.
     */
    public function restore($id)
    {
        $vendor = Vendor::withTrashed()->findOrFail($id);
        $vendor->restore();
        return response()->json($vendor);
    }

    /**
     * Approve a vendor.
     */
    public function approve(Vendor $vendor)
    {
        $vendor->update(['is_approved' => true]);
        return response()->json($vendor);
    }

    /**
     * Get vendor's maintenance requests.
     */
    public function maintenanceRequests(Vendor $vendor)
    {
        $requests = $vendor->maintenanceRequests()->with(['unit', 'tenant'])->paginate(10);
        return response()->json($requests);
    }

    /**
     * Get vendor's repair history.
     */
    public function repairHistory(Vendor $vendor)
    {
        $repairs = $vendor->repairs()->with(['maintenanceRequest'])->paginate(10);
        return response()->json($repairs);
    }

    /**
     * Update vendor rating.
     */
    public function updateRating(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'rating' => 'required|numeric|min:0|max:5',
        ]);

        $vendor->update($validated);
        return response()->json($vendor);
    }
}