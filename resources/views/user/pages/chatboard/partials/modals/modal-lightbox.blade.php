<!-- MODAL: LIGHTBOX XEM ANH LON -->
<div id="modal-lightbox" class="fixed inset-0 bg-slate-950/85 backdrop-blur-sm z-50 hidden items-center justify-center p-4" onclick="closeLightbox()">
    <button type="button" onclick="closeLightbox()" class="absolute top-4 right-4 p-2 text-white/70 hover:text-white rounded-full bg-white/10 hover:bg-white/20 transition-colors z-10" title="Đóng">
        <i data-lucide="x" class="w-6 h-6"></i>
    </button>
    <img id="lightbox-img" src="" class="max-w-[90vw] max-h-[85vh] rounded-2xl object-contain shadow-2xl transition-transform" onclick="event.stopPropagation()">
</div>
