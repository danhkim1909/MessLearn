<!-- MODAL: TRINH XEM TAI LIEU TRUC TIEP (IN-APP DOCUMENT VIEWER) -->
<div id="modal-document-viewer" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden items-center justify-center p-3 sm:p-6">
    <div id="doc-viewer-container" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl flex flex-col w-full max-w-5xl h-[88vh] overflow-hidden transition-all duration-200">
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 shrink-0 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex items-center gap-3 min-w-0 flex-1 mr-4">
                <div id="doc-viewer-badge-icon" class="w-9 h-9 rounded-xl bg-sky-500/10 text-sky-500 flex items-center justify-center shrink-0">
                    <i data-lucide="file-text" id="doc-viewer-header-icon" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 id="doc-viewer-filename" class="font-bold text-sm text-slate-900 dark:text-white truncate">
                        Tên tài liệu
                    </h3>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span id="doc-viewer-ext-badge" class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            PDF
                        </span>
                        <span class="text-[11px] text-slate-400">Xem trực tiếp trên MessLearn</span>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center gap-2 shrink-0">
                <a id="doc-viewer-download-btn" href="#" download="" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors flex items-center gap-1.5" title="Tải tài liệu về máy">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">Tải về</span>
                </a>
                <button type="button" onclick="toggleDocViewerFullscreen()" id="doc-viewer-fullscreen-btn" class="p-2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Toàn màn hình">
                    <i data-lucide="maximize" class="w-4 h-4" id="doc-viewer-fs-icon"></i>
                </button>
                <button type="button" onclick="closeDocumentViewer()" class="p-2 text-slate-400 hover:text-rose-500 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Đóng">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Body Content Viewer -->
        <div class="flex-1 min-h-0 relative bg-slate-100/50 dark:bg-slate-950/50 flex flex-col overflow-hidden">
            <!-- Loading Spinner -->
            <div id="doc-viewer-loading" class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 dark:bg-slate-900/80 z-20 transition-opacity">
                <div class="w-10 h-10 border-4 border-sky-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Đang tải tài liệu...</p>
            </div>

            <!-- 1. PDF Viewer Iframe -->
            <iframe id="doc-viewer-pdf-frame" src="" class="w-full h-full border-0 hidden"></iframe>

            <!-- 2. Markdown Viewer -->
            <div id="doc-viewer-markdown-wrap" class="hidden flex-1 overflow-y-auto p-6 sm:p-10">
                <div id="doc-viewer-markdown-content" class="max-w-4xl mx-auto bg-white dark:bg-slate-900 p-6 sm:p-10 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-800 text-slate-800 dark:text-slate-200 text-sm leading-relaxed space-y-3"></div>
            </div>

            <!-- 3. Source Code / Plain Text Viewer -->
            <div id="doc-viewer-code-wrap" class="hidden flex-1 overflow-auto p-4 sm:p-6">
                <div class="max-w-5xl mx-auto bg-slate-950 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-2 bg-slate-900/90 border-b border-slate-800 text-[11px] text-slate-400 font-mono">
                        <span id="doc-viewer-code-lang">source code</span>
                        <span id="doc-viewer-code-lines">0 dòng</span>
                    </div>
                    <pre class="p-4 overflow-x-auto text-xs font-mono text-slate-200 leading-relaxed select-text"><code id="doc-viewer-code-content"></code></pre>
                </div>
            </div>

            <!-- 4. CSV Table Viewer -->
            <div id="doc-viewer-table-wrap" class="hidden flex-1 overflow-auto p-4 sm:p-6">
                <div class="max-w-6xl mx-auto bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table id="doc-viewer-table-content" class="w-full text-xs text-left border-collapse"></table>
                    </div>
                </div>
            </div>

            <!-- 5. Error Fallback -->
            <div id="doc-viewer-error" class="hidden absolute inset-0 flex flex-col items-center justify-center p-6 text-center">
                <div class="w-14 h-14 rounded-2xl bg-rose-500/10 text-rose-500 flex items-center justify-center mb-3">
                    <i data-lucide="alert-triangle" class="w-7 h-7"></i>
                </div>
                <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200 mb-1">Không thể tải trước tài liệu này</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mb-4">Bạn có thể tải trực tiếp file về máy tính để mở bằng ứng dụng chuyên dụng.</p>
                <a id="doc-viewer-fallback-download" href="#" download="" class="px-4 py-2 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Tải về máy</span>
                </a>
            </div>
        </div>
    </div>
</div>
