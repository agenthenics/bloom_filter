<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\BloomFilter;

class UserController extends Controller
{
    private $bloomFilter;

    public function __construct()
    {
        // Initialize the Bloom Filter
        $this->bloomFilter = new BloomFilter(1000, 5);

        // Populate the Bloom Filter with existing usernames
        $existingUsernames = User::pluck('name')->toArray();
        foreach ($existingUsernames as $username) {
            $this->bloomFilter->add($username);
        }
    }

    public function store(Request $request)
    {
        $username = $request->input('name');

        // Step 1: Check if the username might exist in the Bloom Filter
        if ($this->bloomFilter->contains($username)) {
            // Step 2: Double-check in the database to avoid false positives
            if (User::where('name', $username)->exists()) {
                return response()->json(['message' => 'Username already exists!'], 400);
            }
        }

        // Step 3: Insert the user if not found
        $user = User::create([
            'name' => $username,
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);

        // Step 4: Add the username to the Bloom Filter
        $this->bloomFilter->add($username);

        return response()->json(['message' => 'User created successfully!', 'user' => $user]);
    }
}