<div class="offcanvas offcanvas-end" tabindex="-1" id="proposalSent{{ $lead->id }}" aria-labelledby="historyOffcanvasLabel{{ $lead->id }}" style="width: 400px;">
    
    <div class="offcanvas-header border-bottom bg-light py-3">
        <h6 class="offcanvas-title d-flex align-items-center gap-2 fw-bold text-dark" id="historyOffcanvasLabel{{ $lead->id }}">
            <i class="fa-regular fa-comment-dots text-secondary"></i> 
            History: <span class="text-capitalize">{{ optional($lead->user)->name ?? 'User' }}</span>
        </h6>
        <button type="button" class="btn-close text-reset cancel-offcanvas" data-id="{{ $lead->id }}" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    
    <div class="offcanvas-body p-3" style="background-color: #f4f6f8;">
        
        @forelse ($lead->messages->sortByDesc('created_at') as $message)
            <div class="card border-0 shadow-sm mb-3 rounded-3">
                <div class="card-body p-3">
                    
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        @if($message->bucket)
                            <span class="badge border text-muted fw-normal" style="background-color: #f8f9fa;">{{ $message->bucket }}</span>
                        @endif
                        @if($message->status)
                            <span class="badge border text-muted fw-normal" style="background-color: #f8f9fa;">{{ $message->status }}</span>
                        @endif
                    </div>
                    
                    <p class="text-dark mb-2" style="font-size: 14px; line-height: 1.5; word-wrap: break-word;">
                        {{ $message->message }}
                    </p>

                    @if($message->next_followup_date)
                        <div class="p-2 mb-3 rounded-1" style="background-color: #fef5e7; border-left: 3px solid #f47b20; font-size: 13px;">
                            <span class="text-dark">Follow-up: {{ \Carbon\Carbon::parse($message->next_followup_date)->format('d M y, h:i A') }}</span>
                        </div>
                    @endif

                     @if($message->call_recording)
                        <div class="mt-2 p-1 rounded d-flex align-items-center gap-2"
                            style="background:#e9ecef;">

                            <!-- Hidden Audio -->
                            @if($message->call_recording)
                                <audio controls style="width:100%; height:30px;">
                                    <source src="{{ asset('storage/' . $message->call_recording) }}" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                            @endif
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top" style="font-size: 12px;">
                        <div class="d-flex align-items-center gap-1" style="color: #3b82f6;">
                            <i class="fa-regular fa-user"></i> {{ $message->user->name ?? 'Unknown' }}
                        </div>
                        <div class="d-flex align-items-center gap-1 text-muted">
                            <i class="fa-regular fa-clock"></i> {{ $message->created_at->format('d M y, h:i A') }}
                        </div>
                    </div>

                </div>
            </div>
        @empty
            <div class="text-center p-5">
                <i class="fa-regular fa-folder-open text-muted fs-1 mb-3 opacity-50"></i>
                <p class="text-muted small">No history available for this lead.</p>
            </div>
        @endforelse

    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Check if modal was open before refresh
    var openModalId = localStorage.getItem("openModal");
    if (openModalId) {
        var modalEl = document.getElementById(openModalId);
        if (modalEl) {
            var modal = new bootstrap.Offcanvas(modalEl);
            modal.show();
        }
    }

    // Save modal state on open
    document.querySelectorAll(".open-callback").forEach(btn => {
        btn.addEventListener("click", function() {
            let id = "proposalSent" + this.getAttribute("data-id");
            localStorage.setItem("openModal", id);
        });
    });

    // Clear state only when cancel/close is clicked
    document.querySelectorAll(".cancel-offcanvas").forEach(btn => {
        btn.addEventListener("click", function() {
            localStorage.removeItem("openModal");
            var offcanvasEl = document.getElementById("proposalSent" + this.getAttribute("data-id"));
            if (offcanvasEl) {
                var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                if (offcanvas) offcanvas.hide();
            }
        });
    });
});
</script>