    // ==========================================
    // LÀM BÀI TRẮC NGHIỆM / KHẢO SÁT
    // ==========================================
    let currentActiveFormId = null;

    async function openQuizRunner(formId) {
        currentActiveFormId = formId;
        document.getElementById('quiz-run-container').innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-sky-500"></div></div>';
        document.getElementById('quiz-run-container').style.display = 'block';
        document.getElementById('quiz-result-container').classList.add('hidden');
        document.getElementById('btn-submit-quiz').style.display = 'flex';
        
        openModal('modal-take-quiz');
        
        try {
            const res = await fetch(\{{ url('conversation') }}/{{ $activeConversation->id ?? 0 }}/quiz/\\);
            if (!res.ok) {
                Toastify({ text: "Không thể tải đề bài", style: { background: "#f43f5e" } }).showToast();
                return;
            }
            const data = await res.json();
            
            document.getElementById('quiz-run-title').innerText = data.title;
            document.getElementById('quiz-run-desc').innerText = data.description || '';
            
            renderQuizForm(data.schema);
        } catch (err) {
            Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
        }
    }

    function renderQuizForm(schema) {
        const container = document.getElementById('quiz-run-container');
        container.innerHTML = '';
        
        if (!schema || !schema.questions) return;
        
        schema.questions.forEach((q, idx) => {
            let optionsHtml = '';
            
            if (q.type === 'text') {
                optionsHtml = \<textarea name="ans_\" rows="3" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-sky-500 focus:bg-white transition-all resize-none" placeholder="Nhập câu trả lời của bạn..."></textarea>\;
            } else {
                const inputType = q.type === 'radio' ? 'radio' : 'checkbox';
                q.options.forEach(opt => {
                    optionsHtml += \
                        <label class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 border border-transparent hover:border-slate-200 dark:hover:border-slate-700 cursor-pointer transition-all group">
                            <input type="\" name="ans_\" value="\" class="w-4 h-4 text-sky-500 border-slate-300 focus:ring-sky-500">
                            <span class="text-sm text-slate-700 dark:text-slate-300 font-medium select-none">\</span>
                        </label>
                    \;
                });
            }
            
            const pointsText = q.points > 0 ? \<span class="ml-2 px-2 py-0.5 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-[10px] font-bold rounded-md">\ điểm</span>\ : '';

            container.innerHTML += \
                <div class="p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm mb-4 question-block" data-qid="\" data-qtype="\">
                    <h4 class="font-bold text-slate-900 dark:text-white mb-4 flex items-start gap-2">
                        <span class="shrink-0 w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-900/50 text-sky-500 flex items-center justify-center text-xs">\</span>
                        <span>\ \</span>
                    </h4>
                    <div class="space-y-1 ml-8">
                        \
                    </div>
                </div>
            \;
        });
    }

    async function submitQuiz() {
        if (!currentActiveFormId) return;
        
        // Thu thập đáp án
        const answers = {};
        const blocks = document.querySelectorAll('.question-block');
        
        blocks.forEach(block => {
            const qId = block.getAttribute('data-qid');
            const qType = block.getAttribute('data-qtype');
            
            if (qType === 'text') {
                answers[qId] = block.querySelector('textarea').value.trim();
            } else {
                const checked = Array.from(block.querySelectorAll('input:checked')).map(el => el.value);
                if (qType === 'radio') {
                    answers[qId] = checked[0] || null;
                } else {
                    answers[qId] = checked;
                }
            }
        });
        
        const btn = document.getElementById('btn-submit-quiz');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Đang nộp...';
        btn.disabled = true;
        
        try {
            const res = await fetch(\{{ url('conversation') }}/{{ $activeConversation->id ?? 0 }}/quiz/\/submit\, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ answers })
            });
            
            const data = await res.json();
            
            if (res.ok) {
                document.getElementById('quiz-run-container').style.display = 'none';
                document.getElementById('btn-submit-quiz').style.display = 'none';
                
                const resultContainer = document.getElementById('quiz-result-container');
                resultContainer.classList.remove('hidden');
                
                document.getElementById('quiz-score').innerText = \\ / \\;
                Toastify({ text: "Nộp bài thành công!", style: { background: "#10b981" } }).showToast();
            } else {
                Toastify({ text: data.message || "Lỗi khi nộp bài", style: { background: "#f59e0b" } }).showToast();
            }
        } catch (err) {
            Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            lucide.createIcons();
        }
    }
