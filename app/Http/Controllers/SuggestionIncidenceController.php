<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use App\Models\Incidence;
use App\Models\Student;
use App\Models\User;
use App\Models\OwnerDetail;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SuggestionIncidenceController extends Controller
{
    /**
     * Store a new suggestion
     */
    public function storeSuggestion(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $user = Auth::user();
        
        // Find student record
        $student = null;
        if ($user->role === 'student') {
            $username = preg_replace('/[^0-9]/', '', $user->username);
            $student = Student::where('phone', $username)
                ->orWhere('phone', $user->username)
                ->orWhere('email', $user->email)
                ->first();
        }

        $suggestion = Suggestion::create([
            'student_id' => $student ? $student->id : null,
            'user_id' => $user->id,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        // Send SMS to owner
        $this->notifyOwner('suggestion', $suggestion, $student, $user);

        return response()->json([
            'success' => true,
            'message' => 'Suggestion yako imetumwa kwa mafanikio. Asante!',
        ]);
    }

    /**
     * Store a new incidence
     */
    public function storeIncidence(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $user = Auth::user();
        
        // Find student record
        $student = null;
        if ($user->role === 'student') {
            $username = preg_replace('/[^0-9]/', '', $user->username);
            $student = Student::where('phone', $username)
                ->orWhere('phone', $user->username)
                ->orWhere('email', $user->email)
                ->first();
        }

        $incidence = Incidence::create([
            'student_id' => $student ? $student->id : null,
            'user_id' => $user->id,
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'pending',
        ]);

        // Send SMS to owner
        $this->notifyOwner('incidence', $incidence, $student, $user);

        return response()->json([
            'success' => true,
            'message' => 'Incidence yako imetumwa kwa mafanikio. Tutachukua hatua haraka!',
        ]);
    }

    /**
     * Get all suggestions (for owner)
     */
    public function getSuggestions()
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $suggestions = Suggestion::with(['student', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($suggestions);
    }

    /**
     * Get all incidences (for owner)
     */
    public function getIncidences()
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $incidences = Incidence::with(['student', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($incidences);
    }

    /**
     * Mark suggestion as read and respond
     */
    public function markSuggestionRead($id, Request $request)
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $suggestion = Suggestion::with(['student'])->findOrFail($id);
        $suggestion->update([
            'status' => 'read',
            'read_at' => Carbon::now(),
            'response' => $request->response ?? null,
        ]);

        // Send SMS to student if response provided
        if ($request->response && $suggestion->student) {
            $this->notifyStudent('suggestion', $suggestion);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark incidence as resolved
     */
    public function markIncidenceResolved($id, Request $request)
    {
        if (Auth::user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $incidence = Incidence::with(['student'])->findOrFail($id);
        $incidence->update([
            'status' => 'resolved',
            'response' => $request->response ?? null,
            'resolved_at' => Carbon::now(),
        ]);

        // Send SMS to student if response provided
        if ($request->response && $incidence->student) {
            $this->notifyStudent('incidence', $incidence);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get student's own suggestions and incidences
     */
    public function getStudentSuggestions()
    {
        $user = Auth::user();
        if ($user->role !== 'student') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Find student record
        $username = preg_replace('/[^0-9]/', '', $user->username);
        $student = Student::where('phone', $username)
            ->orWhere('phone', $user->username)
            ->orWhere('email', $user->email)
            ->first();

        if (!$student) {
            return response()->json(['suggestions' => [], 'incidences' => []]);
        }

        $suggestions = Suggestion::where('student_id', $student->id)
            ->orWhere('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $incidences = Incidence::where('student_id', $student->id)
            ->orWhere('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'suggestions' => $suggestions,
            'incidences' => $incidences
        ]);
    }

    /**
     * Send SMS notification to owner
     */
    private function notifyOwner($type, $item, $student, $user)
    {
        try {
            $owner = User::where('role', 'owner')->first();
            if (!$owner) {
                return;
            }

            $ownerDetail = OwnerDetail::where('user_id', $owner->id)->first();
            if (!$ownerDetail || !$ownerDetail->phone_number) {
                return;
            }

            $smsService = new SmsService();
            $studentName = $student ? $student->full_name : $user->name;
            $studentPhone = $student ? $student->phone : ($user->username ?? 'N/A');

            if ($type === 'suggestion') {
                $message = "SUGGESTION MPYA kutoka {$studentName} ({$studentPhone}). Jina: {$item->subject}. Ujumbe: " . substr($item->message, 0, 100) . "...";
            } else {
                $priorityText = [
                    'low' => 'Chini',
                    'medium' => 'Wastani',
                    'high' => 'Juu',
                    'urgent' => 'Dharura'
                ];
                $message = "INCIDENCE MPYA! Kutoka {$studentName} ({$studentPhone}). Kipaumbele: {$priorityText[$item->priority]}. Jina: {$item->subject}. Maelezo: " . substr($item->description, 0, 100) . "...";
            }

            $smsService->sendSms($ownerDetail->phone_number, $message);
        } catch (\Exception $e) {
            \Log::error('Failed to send SMS notification to owner: ' . $e->getMessage());
        }
    }

    /**
     * Send SMS notification to student when owner responds
     */
    private function notifyStudent($type, $item)
    {
        try {
            if (!$item->student || !$item->response) {
                return;
            }

            $smsService = new SmsService();
            $student = $item->student;
            
            if ($type === 'suggestion') {
                $message = "Habari {$student->full_name}. Owner amejibu suggestion yako:\n";
                $message .= "Jina: {$item->subject}\n";
                $message .= "Jibu: {$item->response}";
            } else {
                $message = "Habari {$student->full_name}. Owner amejibu incidence yako:\n";
                $message .= "Jina: {$item->subject}\n";
                $message .= "Jibu: {$item->response}";
            }

            $smsService->sendSms($student->phone, $message);
        } catch (\Exception $e) {
            \Log::error('Failed to send SMS notification to student: ' . $e->getMessage());
        }
    }
}

