<?php

namespace App\Http\Controllers\User;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    public function store(Request $request, Conversation $conversation)
    {
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
                $formId = DB::table('forms')->insertGetId([
                    'title' => $request->title,
                    'description' => $request->description,
                    'type' => $request->type ?? 'quiz',
                    'schema' => json_encode($request->schema),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $msg = Message::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => Auth::id(),
                    'type' => 'quiz',
                    'body' => 'Đã tạo bài: ' . $request->title,
                    'form_id' => $formId,
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

    public function show(Conversation $conversation, $formId)
    {
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $form = DB::table('forms')->where('id', $formId)->first();
        if (!$form) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $schema = json_decode($form->schema, true);
        
        // Remove correct_answers for students
        if (isset($schema['questions'])) {
            foreach ($schema['questions'] as &$question) {
                if (isset($question['correct_answers'])) {
                    unset($question['correct_answers']);
                }
            }
        }

        return response()->json([
            'id' => $form->id,
            'title' => $form->title,
            'description' => $form->description,
            'schema' => $schema,
        ]);
    }

    public function submit(Request $request, Conversation $conversation, $formId)
    {
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $form = DB::table('forms')->where('id', $formId)->first();
        if (!$form) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $existing = DB::table('form_submissions')
            ->where('form_id', $formId)
            ->where('user_id', Auth::id())
            ->first();
            
        if ($existing) {
            return response()->json(['message' => 'Bạn đã nộp bài này rồi!', 'score' => $existing->score, 'max_score' => $existing->max_score], 400);
        }

        $answers = $request->input('answers', []);
        $schema = json_decode($form->schema, true);
        
        $totalScore = 0;
        $maxScore = 0;
        
        if (isset($schema['questions'])) {
            foreach ($schema['questions'] as $question) {
                if ($question['type'] === 'text') continue;
                
                $maxScore += $question['points'] ?? 1;
                
                $qId = $question['id'];
                $userAns = $answers[$qId] ?? [];
                if (!is_array($userAns)) $userAns = [$userAns];
                
                $correctAns = $question['correct_answers'] ?? [];
                
                sort($userAns);
                sort($correctAns);
                
                if ($userAns == $correctAns && count($correctAns) > 0) {
                    $totalScore += $question['points'] ?? 1;
                }
            }
        }

        DB::table('form_submissions')->insert([
            'form_id' => $formId,
            'user_id' => Auth::id(),
            'answers' => json_encode($answers),
            'score' => $totalScore,
            'max_score' => $maxScore,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Thành công',
            'score' => $totalScore,
            'max_score' => $maxScore
        ]);
    }
}
