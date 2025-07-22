<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Display the feedback form.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get the token from the request
        $token = $request->query('token');
        $email = $request->query('email');
        
        // Check if the token and email are provided
        if (!$token || !$email) {
            return redirect()->route('feedback.invalid_token');
        }
        
        // Check if the token is valid
        $client = Client::where('email', $email)
            ->where('verification_token', $token)
            ->first();
        
        // If client not found with this token and email, show invalid token page
        if (!$client) {
            return redirect()->route('feedback.invalid_token');
        }
        
        // Check if the client has already submitted feedback - skip for test tokens
        if (!(substr($token, 0, 1) === 'T' && strlen($token) === 6) && Feedback::where('client_id', $client->id)->exists()) {
            return view('feedback.already_submitted');
        }
        
        return view('feedback.form', compact('client', 'token', 'email'));
    }
    
    /**
     * Store a new feedback submission.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'wifi_rating' => 'required|integer|min:1|max:5',
            'hotel_rating' => 'required|integer|min:1|max:5',
            'room_rating' => 'required|integer|min:1|max:5',
            'service_rating' => 'required|integer|min:1|max:5',
            'food_rating' => 'required|integer|min:1|max:5',
            'is_satisfied' => 'required|boolean',
            'visit_again' => 'required|boolean',
            'comments' => 'nullable|string|max:1000',
            'suggestion' => 'nullable|string|max:1000',
            'token' => 'required|string',
            'email' => 'required|email',
        ]);
        
        // Find client by email and token
        $client = Client::where('email', $validated['email'])
            ->where('verification_token', $validated['token'])
            ->first();
        
        // If no valid client found, redirect to invalid token page
        if (!$client) {
            return redirect()->route('feedback.invalid_token');
        }
        
        // Check if the client has already submitted feedback - skip for test tokens
        if (!(substr($validated['token'], 0, 1) === 'T' && strlen($validated['token']) === 6) && Feedback::where('client_id', $client->id)->exists()) {
            return redirect()->route('feedback.already_submitted');
        }
        
        // Create new feedback entry
        $feedback = new Feedback();
        $feedback->client_id = $client->id;
        $feedback->email = $client->email;
        $feedback->wifi_rating = $validated['wifi_rating'];
        $feedback->hotel_rating = $validated['hotel_rating'];
        $feedback->room_rating = $validated['room_rating'];
        $feedback->service_rating = $validated['service_rating'];
        $feedback->food_rating = $validated['food_rating'];
        $feedback->is_satisfied = $validated['is_satisfied'];
        $feedback->visit_again = $validated['visit_again'];
        $feedback->comments = $validated['comments'];
        $feedback->suggestion = $validated['suggestion'];
        $feedback->ip_address = $request->ip();
        $feedback->user_agent = $request->userAgent();
        $feedback->save();
        
        // Redirect to thank you page
        return redirect()->route('feedback.thank_you');
    }
    
    /**
     * Display the thank you page after feedback submission.
     *
     * @return \Illuminate\Http\Response
     */
    public function thankYou()
    {
        return view('feedback.thank-you');
    }
    
    /**
     * Display the already submitted page.
     *
     * @return \Illuminate\Http\Response
     */
    public function alreadySubmitted()
    {
        return view('feedback.already_submitted');
    }
    
    /**
     * Display the invalid token page.
     *
     * @return \Illuminate\Http\Response
     */
    public function invalidToken()
    {
        return view('feedback.invalid_token');
    }
    
    /**
     * Display the admin feedback dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        // Check if user is authenticated and is admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('login');
        }
        
        // Get all feedback entries, paginated
        $feedback = Feedback::with('client')->orderBy('created_at', 'desc')->paginate(10);
        
        // Calculate averages for statistics
        $averages = [
            'total' => Feedback::count(),
            'wifi' => Feedback::whereNotNull('wifi_rating')->avg('wifi_rating') ?? 0,
            'hotel' => Feedback::whereNotNull('hotel_rating')->avg('hotel_rating') ?? 0,
            'room' => Feedback::whereNotNull('room_rating')->avg('room_rating') ?? 0,
            'service' => Feedback::whereNotNull('service_rating')->avg('service_rating') ?? 0,
            'food' => Feedback::whereNotNull('food_rating')->avg('food_rating') ?? 0,
            'satisfaction_rate' => $this->calculateSatisfactionRate(),
        ];
        
        return view('dashboard.feedback', compact('feedback', 'averages'));
    }
    
    /**
     * Calculate the satisfaction rate as a percentage.
     *
     * @return float
     */
    private function calculateSatisfactionRate()
    {
        $totalRated = Feedback::whereNotNull('is_satisfied')->count();
        
        if ($totalRated === 0) {
            return 0;
        }
        
        $satisfied = Feedback::where('is_satisfied', true)->count();
        return ($satisfied / $totalRated) * 100;
    }
} 