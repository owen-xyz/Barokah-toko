{{-- Skeleton loader saat produk sedang dimuat --}}
@for ($i = 0; $i < 8; $i++)
<div class="product-card skeleton-card">
    <div class="skeleton skeleton-img"></div>
    <div class="skeleton skeleton-title"></div>
    <div class="skeleton skeleton-price"></div>
</div>
@endfor
