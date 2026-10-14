@if($paginator->hasPages())
    <div class="mt-3 d-flex justify-content-center">
        {{ $paginator->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
@endif