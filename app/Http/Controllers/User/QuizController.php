<?php

namespace App\Http\Controllers\User;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    // --- Táº¡o bĂ i kiá»ƒm tra ---
    public function store(Request $request, Conversation $conversation)
    {
        \Illuminate\Support\Facades\Log::info('Quiz payload:', $request->all());
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'schema' => 'required',
        ]);

        try {
            $message = DB::transaction(function () use ($request, $conversation) {
                $quiz = Quiz::create([
                    'title' => $request->title,
                    'description' => $request->description,
                ]);

                if (isset($request->schema['questions'])) {
                    foreach ($request->schema['questions'] as $index => $qData) {
                        $question = $quiz->questions()->create([
                            'question_text' => $qData['title'],
                            'type' => $qData['type'],
                            'points' => $qData['points'] ?? 1,
                            'correct_text_answer' => $qData['type'] === 'text' ? ($qData['correct_answers'][0] ?? null) : null,
                            'order' => $index,
                        ]);

                        if (in_array($qData['type'], ['radio', 'checkbox'])) {
                            foreach ($qData['options'] as $opt) {
                                $optId = $opt['id'] ?? null;
                                $optText = $opt['text'] ?? '';
                                $isCorrect = in_array($optId, $qData['correct_answers'] ?? []);
                                $question->options()->create([
                                    'option_text' => $optText,
                                    'is_correct' => $isCorrect,
                                ]);
                            }
                        }
                    }
                }

                $msg = Message::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => Auth::id(),
                    'type' => 'quiz',
                    'body' => 'Đã tạo bài: ' . $request->title,
                    'quiz_id' => $quiz->id,
                ]);
                
                $msg->load('user');
                return $msg;
            });

            broadcast(new MessageSent($message))->toOthers();

            return response()->json($message, 201);
            
        } catch (\Exception $e) {
            Log::error("Error: " . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    // --- Láº¥y thĂ´ng tin bĂ i kiá»ƒm tra ---
    public function show(Conversation $conversation, $quizId)
    {
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $quiz = Quiz::with('questions.options')->findOrFail($quizId);

        $schema = [
            'questions' => $quiz->questions->map(function ($q) {
                return [
                    'id' => $q->id,
                    'type' => $q->type,
                    'title' => $q->question_text,
                    'points' => $q->points,
                    'options' => $q->options->map(function ($opt) {
                        return [
                            'id' => $opt->id,
                            'text' => $opt->option_text,
                        ];
                    })->toArray(),
                ];
            })->toArray()
        ];

        return response()->json([
            'id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'schema' => $schema,
        ]);
    }

    // --- Ná»™p bĂ i vĂ  cháº¥m Ä‘iá»ƒm ---
    public function submit(Request $request, Conversation $conversation, $quizId)
    {
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $quiz = Quiz::with('questions.options')->findOrFail($quizId);

        $existing = QuizSubmission::where('quiz_id', $quizId)
            ->where('user_id', Auth::id())
            ->first();
            
        if ($existing) {
            return response()->json([
                'message' => 'Bạn đã nộp bài này rồi!', 
                'score' => $existing->total_score, 
                'max_score' => $quiz->questions->sum('points')
            ], 400);
        }

        $answers = $request->input('answers', []);
        
        $totalScore = 0;
        $maxScore = 0;
        
        DB::transaction(function () use ($quiz, $answers, &$totalScore, &$maxScore) {
            $submission = QuizSubmission::create([
                'quiz_id' => $quiz->id,
                'user_id' => Auth::id(),
                'total_score' => 0,
                'completed_at' => now(),
            ]);

            foreach ($quiz->questions as $question) {
                $maxScore += $question->points;
                $userAns = $answers[$question->id] ?? null;
                $isCorrect = false;
                $pointsEarned = 0;
                
                if ($question->type === 'text') {
                    $userText = trim(strtolower((string)$userAns));
                    $correctText = trim(strtolower((string)$question->correct_text_answer));
                    if ($userText !== '' && $userText === $correctText) {
                        $isCorrect = true;
                        $pointsEarned = $question->points;
                    }

                    $submission->answers()->create([
                        'quiz_question_id' => $question->id,
                        'text_answer' => (string)$userAns,
                        'is_correct' => $isCorrect,
                        'points_earned' => $pointsEarned,
                    ]);
                } else if ($question->type === 'radio') {
                    $selectedId = $userAns ? (int)$userAns : null;
                    if ($selectedId) {
                        $option = $question->options->where('id', $selectedId)->first();
                        if ($option && $option->is_correct) {
                            $isCorrect = true;
                            $pointsEarned = $question->points;
                        }
                        $submission->answers()->create([
                            'quiz_question_id' => $question->id,
                            'selected_option_id' => $selectedId,
                            'is_correct' => $isCorrect,
                            'points_earned' => $pointsEarned,
                        ]);
                    }
                } else if ($question->type === 'checkbox') {
                    $selectedIds = is_array($userAns) ? array_map('intval', $userAns) : [];
                    $correctIds = $question->options->where('is_correct', true)->pluck('id')->toArray();
                    
                    sort($selectedIds);
                    sort($correctIds);
                    
                    if (!empty($selectedIds) && $selectedIds === $correctIds) {
                        $isCorrect = true;
                        $pointsEarned = $question->points;
                    }

                    foreach ($selectedIds as $sId) {
                        $submission->answers()->create([
                            'quiz_question_id' => $question->id,
                            'selected_option_id' => $sId,
                            'is_correct' => in_array($sId, $correctIds),
                            'points_earned' => 0, // for checkbox we'll just track total at submission or per answer
                        ]);
                    }
                    
                    // Update the last answer with points earned for the question, or distribute it.
                    // A simple way is we already calculate total score.
                }

                $totalScore += $pointsEarned;
            }

            $submission->update(['total_score' => $totalScore]);
        });

        return response()->json([
            'message' => 'Thành công',
            'score' => $totalScore,
            'max_score' => $maxScore
        ]);
    }

    public function results(Conversation $conversation, $quizId)
    {
        $userId = Auth::id();

        if (!$conversation->participants()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $quiz = Quiz::with([
            'message', 
            'submissions.user', 
            'submissions.answers.question', 
            'submissions.answers.selectedOption',
            'questions.options'
        ])->findOrFail($quizId);

        $isOwner = $quiz->message && $quiz->message->user_id == $userId;

        $submissions = $quiz->submissions->sortByDesc('total_score')->values()->map(function($sub) use ($isOwner) {
            $data = [
                'id' => $sub->id,
                'user' => [
                    'id' => $sub->user->id,
                    'name' => $sub->user->name,
                    'email' => $sub->user->email,
                ],
                'total_score' => $sub->total_score,
                'completed_at' => $sub->completed_at ? $sub->completed_at->format('d/m/Y H:i') : null,
            ];

            if ($isOwner) {
                $data['answers'] = $sub->answers->map(function($ans) {
                    return [
                        'question_text' => $ans->question->question_text,
                        'is_correct' => $ans->is_correct,
                        'points_earned' => $ans->points_earned,
                        'answer_text' => $ans->question->type === 'text' ? $ans->text_answer : ($ans->selectedOption ? $ans->selectedOption->option_text : ''),
                    ];
                });
            }

            return $data;
        });

        return response()->json([
            'quiz_title' => $quiz->title,
            'is_owner' => $isOwner,
            'submissions' => $submissions
        ]);
    }
}

