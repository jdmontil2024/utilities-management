<div id="photoGallery">
    @if($photos && $photos->count() > 0)
    <div class="photo-gallery">
        @foreach($photos as $photo)
        <div class="photo-card @if($photo->is_primary) primary @endif" data-category="{{ $photo->category }}" data-photo-id="{{ $photo->id }}">
            <img src="{{ Storage::url($photo->path) }}" alt="{{ $photo->description }}" class="photo-image">
            <div class="photo-details">
                <span class="photo-category">{{ $categoryLabels[$photo->category] ?? ucfirst($photo->category) }}</span>
                @if($photo->is_primary)
                <span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; margin-left: 5px;">Primary</span>
                @endif
                <div class="photo-description">{{ $photo->description ?? 'No description' }}</div>
                <div class="photo-meta">
                    <span>📅 {{ $photo->created_at->format('M d, Y') }}</span>
                    <span>👤 {{ $photo->uploaded_by ?? 'Admin' }}</span>
                </div>
                <div class="photo-actions">
                    <button onclick="setAsPrimary({{ $photo->id }})" class="btn-sm" style="background: #3498db; color: white; flex: 1;">
                        {{ $photo->is_primary ? '✓ Primary' : 'Set Primary' }}
                    </button>
                    <button onclick="viewPhoto('{{ Storage::url($photo->path) }}', '{{ $photo->description }}')" class="btn-sm" style="background: #27ae60; color: white; flex: 1;">
                        👁️ View
                    </button>
                    <button onclick="deletePhoto({{ $photo->id }})" class="btn-sm" style="background: #e74c3c; color: white; flex: 1;">
                        🗑️ Delete
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="no-data">
        <div style="font-size: 48px; margin-bottom: 15px;">🖼️</div>
        <h3>No Photos Uploaded Yet</h3>
        <p>Upload photos to showcase this building</p>
    </div>
    @endif
</div>