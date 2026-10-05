<script>
let questionCount = 0;

    function renderQuestionHTML(qId, index) {
        return `
            <div class="p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl relative quiz-question-item shadow-sm mb-4 transition-all" data-id="${qId}">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-sky-100 dark:bg-sky-900/50 text-sky-500 flex items-center justify-center font-extrabold text-xs">Câu ${index}</span>
                        <select class="q-type bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg px-3 py-1.5 focus:outline-none focus:border-sky-500 transition-all cursor-pointer" onchange="changeQuestionType('${qId}')">
                            <option value="radio">Trắc nghiệm (1 đáp án đúng)</option>
                            <option value="checkbox">Trắc nghiệm (Nhiều đáp án đúng)</option>
                            <option value="text">Tự luận ngắn (Khảo sát)</option>
                        </select>
                    </div>
                    <button type="button" onclick="this.closest('.quiz-question-item').remove()" class="text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 p-1.5 rounded-lg transition-all" title="Xóa câu hỏi">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <input type="text" class="q-title w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-900 dark:text-white focus:outline-none focus:border-sky-500 focus:bg-white transition-all" placeholder="Nhập nội dung câu hỏi..." required>
                    </div>
                    
                    <div class="q-options-container space-y-2.5 ml-2" id="options_${qId}">
                    </div>

                    <button type="button" onclick="addOption('${qId}')" class="btn-add-opt text-[11px] font-bold text-sky-500 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-900/30 px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 mt-2 transition-all">
                        <i data-lucide="plus" class="w-3 h-3"></i> Thêm đáp án
                    </button>
                </div>
            </div>
        `;
    }

    function renderOptionHTML(qId, optId, type) {
        const inputType = type === 'radio' ? 'radio' : 'checkbox';
        return `
            <div class="flex items-center gap-3 option-item group relative">
                <div class="relative flex items-center justify-center cursor-pointer" title="Đánh dấu đây là đáp án đúng">
                    <input type="${inputType}" name="correct_${qId}" value="${optId}" class="w-4 h-4 text-emerald-500 border-slate-300 focus:ring-emerald-500 cursor-pointer peer">
                </div>
                <input type="text" class="opt-text flex-1 px-3 py-2 bg-transparent border-b border-transparent group-hover:border-slate-200 dark:group-hover:border-slate-700 focus:border-sky-500 text-sm text-slate-700 dark:text-slate-300 focus:outline-none transition-all" placeholder="Nhập lựa chọn...">
                <button type="button" onclick="this.closest('.option-item').remove()" class="text-slate-300 hover:text-rose-500 opacity-0 group-hover:opacity-100 transition-opacity p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        `;
    }

    function addQuizQuestion() {
        questionCount++;
        const qId = `q_${Date.now()}_${questionCount}`;
        const container = document.getElementById('quiz-builder-container');
        
        container.insertAdjacentHTML('beforeend', renderQuestionHTML(qId, questionCount));
        
        addOption(qId);
        addOption(qId);
        
        lucide.createIcons();
    }

    function addOption(qId) {
        const qItem = document.querySelector(`.quiz-question-item[data-id="${qId}"]`);
        const type = qItem.querySelector('.q-type').value;
        const container = document.getElementById(`options_${qId}`);
        const optId = `opt_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`;
        
        container.insertAdjacentHTML('beforeend', renderOptionHTML(qId, optId, type));
        lucide.createIcons();
    }

    function changeQuestionType(qId) {
        const qItem = document.querySelector(`.quiz-question-item[data-id="${qId}"]`);
        const type = qItem.querySelector('.q-type').value;
        const optsContainer = document.getElementById(`options_${qId}`);
        const btnAddOpt = qItem.querySelector('.btn-add-opt');

        if (type === 'text') {
            optsContainer.innerHTML = `<div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-dashed border-slate-300 dark:border-slate-600 text-slate-500 text-xs text-center font-medium">Phần này dành cho người học tự gõ câu trả lời (Dùng làm Khảo sát hoặc Tự luận)</div>`;
            btnAddOpt.style.display = 'none';
        } else {
            optsContainer.innerHTML = '';
            btnAddOpt.style.display = 'inline-flex';
            addOption(qId);
            addOption(qId);
        }
    }

    async function submitCustomForm() {
        const title = document.getElementById('quiz-title').value.trim();
        const desc = document.getElementById('quiz-desc').value.trim();
        
        if (!title) {
            Toastify({ text: "Vui lòng nhập tiêu đề Form", style: { background: "#f59e0b" } }).showToast();
            return;
        }

        const questionItems = document.querySelectorAll('.quiz-question-item');
        if (questionItems.length === 0) {
            Toastify({ text: "Vui lòng thêm ít nhất 1 câu hỏi", style: { background: "#f59e0b" } }).showToast();
            return;
        }

        const schema = {
            settings: { 
                type: 'quiz',
                show_score: true
            },
            questions: []
        };

        let hasError = false;
        let errorMessage = "Vui lòng điền đủ nội dung câu hỏi và đáp án!";

        questionItems.forEach((item) => {
            const qId = item.getAttribute('data-id');
            const qTitle = item.querySelector('.q-title').value.trim();
            const qType = item.querySelector('.q-type').value;

            if (!qTitle) hasError = true;

            const questionData = {
                id: qId,
                type: qType,
                title: qTitle,
                points: qType === 'text' ? 0 : 1,
                options: [],
                correct_answers: []
            };

            if (qType !== 'text') {
                const optItems = item.querySelectorAll('.option-item');
                if (optItems.length < 2) hasError = true;

                let hasCorrectAnswer = false;

                optItems.forEach(optItem => {
                    const inputCheck = optItem.querySelector('input[type="radio"], input[type="checkbox"]');
                    const textInput = optItem.querySelector('.opt-text').value.trim();
                    const optId = inputCheck.value;

                    if (textInput) {
                        questionData.options.push({ id: optId, text: textInput });
                        if (inputCheck.checked) {
                            questionData.correct_answers.push(optId);
                            hasCorrectAnswer = true;
                        }
                    }
                });
                
                if (questionData.options.length < 2) hasError = true;
                
                if (!hasCorrectAnswer) {
                    hasError = true;
                    errorMessage = `Vui lòng tick xanh chọn ít nhất 1 đáp án ĐÚNG cho "${qTitle}"`;
                }
            }

            schema.questions.push(questionData);
        });

        if (hasError) {
            Toastify({ text: errorMessage, style: { background: "#f43f5e" } }).showToast();
            return;
        }

        const payload = {
            title: title,
            description: desc,
            type: 'quiz',
            schema: schema
        };

        try {
            const res = await fetch(`{{ route('app.conversation.quiz.store', $activeConversation->id ?? 0) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                const data = await res.json();
                appendMessageToChat(data);
                closeModal('modal-create-quiz');
                document.getElementById('quiz-title').value = '';
                document.getElementById('quiz-desc').value = '';
                document.getElementById('quiz-builder-container').innerHTML = '';
                questionCount = 0;
                Toastify({ text: "Đã xuất bản bài tập thành công!", style: { background: "#10b981" } }).showToast();
            } else {
                Toastify({ text: "Lỗi lưu Form. Hãy thử lại.", style: { background: "#f43f5e" } }).showToast();
            }
        } catch (err) {
            Toastify({ text: "Lỗi kết nối mạng", style: { background: "#f43f5e" } }).showToast();
        }
    }

    let currentActiveFormId = null;


    async function openQuizLeaderboard(formId) {
        document.getElementById('leaderboard-container').innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-500"></div></div>';
        openModal('modal-quiz-leaderboard');

        try {
            const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation->id ?? 0 }}/quiz/${formId}/results`);
            if (!res.ok) {
                Toastify({ text: "Không thể tải điểm số", style: { background: "#f43f5e" } }).showToast();
                closeModal('modal-quiz-leaderboard');
                return;
            }
            
            const data = await res.json();
            document.getElementById('leaderboard-title').innerText = "Kết quả: " + data.quiz_title;
            
            let html = '';
            
            if (data.submissions.length === 0) {
                html = '<div class="text-center text-slate-500 py-8 text-sm">Chưa có ai nộp bài.</div>';
            } else {
                data.submissions.forEach((sub, index) => {
                    let rankClass = "bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300";
                    if (index === 0) rankClass = "bg-amber-100 dark:bg-amber-900/50 text-amber-500";
                    else if (index === 1) rankClass = "bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-200";
                    else if (index === 2) rankClass = "bg-orange-100 dark:bg-orange-900/50 text-orange-500";

                    let detailsHtml = '';
                    if (data.is_owner && sub.answers) {
                        detailsHtml = `
                            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/60 space-y-3 hidden" id="details-${sub.id}">
                                <h5 class="text-xs font-bold text-slate-500 uppercase">Chi tiết câu trả lời</h5>
                        `;
                        sub.answers.forEach(ans => {
                            const isCorrect = ans.is_correct;
                            const color = isCorrect ? 'text-emerald-500' : 'text-rose-500';
                            const icon = isCorrect ? 'check-circle' : 'x-circle';
                            detailsHtml += `
                                <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-xl text-sm">
                                    <p class="font-medium text-slate-700 dark:text-slate-300 mb-1">${ans.question_text}</p>
                                    <div class="flex items-center gap-2 ${color}">
                                        <i data-lucide="${icon}" class="w-4 h-4"></i>
                                        <span class="font-bold text-xs">${ans.answer_text || '(Không trả lời)'}</span>
                                        <span class="ml-auto text-xs text-slate-400">+${ans.points_earned} đ</span>
                                    </div>
                                </div>
                            `;
                        });
                        detailsHtml += `</div>
                            <button type="button" onclick="document.getElementById('details-${sub.id}').classList.toggle('hidden')" class="mt-2 text-[11px] font-bold text-sky-500 hover:text-sky-600 underline">Xem chi tiết</button>
                        `;
                    }

                    html += `
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 shadow-sm">
                            <div class="flex items-center gap-4">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-sm ${rankClass}">
                                    #${index + 1}
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-sky-100 dark:bg-sky-900/30 text-sky-500 flex items-center justify-center font-bold text-sm shrink-0">
                                    ${sub.user.name.charAt(0).toUpperCase()}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-sm text-slate-900 dark:text-white truncate">${sub.user.name}</h4>
                                    <p class="text-[10px] text-slate-500">${sub.completed_at || 'Không rõ thời gian'}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-xl font-black text-emerald-500">${sub.total_score}</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Điểm</div>
                                </div>
                            </div>
                            ${detailsHtml}
                        </div>
                    `;
                });
            }
            
            document.getElementById('leaderboard-container').innerHTML = html;
            lucide.createIcons();

        } catch (err) {
            Toastify({ text: "Lỗi kết nối", style: { background: "#f43f5e" } }).showToast();
            closeModal('modal-quiz-leaderboard');
        }
    }

    async function openQuizRunner(formId) {
        currentActiveFormId = formId;
        document.getElementById('quiz-run-container').innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-sky-500"></div></div>';
        document.getElementById('quiz-run-container').style.display = 'block';
        document.getElementById('quiz-result-container').classList.add('hidden');
        document.getElementById('btn-submit-quiz').style.display = 'flex';
        
        openModal('modal-take-quiz');
        
        try {
            const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation->id ?? 0 }}/quiz/${formId}`);
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
                optionsHtml = `<textarea name="ans_${q.id}" rows="3" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-sky-500 focus:bg-white transition-all resize-none" placeholder="Nhập câu trả lời của bạn..."></textarea>`;
            } else {
                const inputType = q.type === 'radio' ? 'radio' : 'checkbox';
                q.options.forEach(opt => {
                    optionsHtml += `
                        <label class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 border border-transparent hover:border-slate-200 dark:hover:border-slate-700 cursor-pointer transition-all group">
                            <input type="${inputType}" name="ans_${q.id}" value="${opt.id}" class="w-4 h-4 text-sky-500 border-slate-300 focus:ring-sky-500">
                            <span class="text-sm text-slate-700 dark:text-slate-300 font-medium select-none">${opt.text}</span>
                        </label>
                    `;
                });
            }
            
            const pointsText = q.points > 0 ? `<span class="ml-2 px-2 py-0.5 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-[10px] font-bold rounded-md">${q.points} điểm</span>` : '';

            container.innerHTML += `
                <div class="p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm mb-4 question-block" data-qid="${q.id}" data-qtype="${q.type}">
                    <h4 class="font-bold text-slate-900 dark:text-white mb-4 flex items-start gap-2">
                        <span class="shrink-0 w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-900/50 text-sky-500 flex items-center justify-center text-xs">${idx + 1}</span>
                        <span>${q.title} ${pointsText}</span>
                    </h4>
                    <div class="space-y-1 ml-8">
                        ${optionsHtml}
                    </div>
                </div>
            `;
        });
    }

    async function submitQuiz() {
        if (!currentActiveFormId) return;
        
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
            const res = await fetch(`{{ url('app/conversation') }}/{{ $activeConversation->id ?? 0 }}/quiz/${currentActiveFormId}/submit`, {
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
                
                document.getElementById('quiz-score').innerText = `${data.score} / ${data.max_score}`;
                Toastify({ text: "Nộp bài thành công!", style: { background: "#10b981" } }).showToast();
            } else {
                if (data.score !== undefined) {
                    document.getElementById('quiz-run-container').style.display = 'none';
                    document.getElementById('btn-submit-quiz').style.display = 'none';
                    const resultContainer = document.getElementById('quiz-result-container');
                    resultContainer.classList.remove('hidden');
                    document.getElementById('quiz-score').innerText = `${data.score} / ${data.max_score}`;
                }
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
</script>
