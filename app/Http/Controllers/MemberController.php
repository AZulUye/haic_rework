<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    /**
     * Get all members.
     */
    public function getAllMembers(): JsonResponse
    {
        try {
            $members = Member::all();

            return response()->json($members);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getMemberInformation($memberId): JsonResponse
    {
        try {
            $member = Member::with(['user' => function ($query) {
                $query->select('id', 'email');
            }])->findOrFail($memberId);

            if (! $member) {
                return response()->json(['message' => 'Member Not Found'], 404);
            }

            return response()->json($member, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function edit(): Response
    {
        $user = Auth::user();
        $member = $user->member;

        return Inertia::render('profile/index', [
            'user' => $user,
            'member' => $member,
        ]);
    }

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $member = $user->member;

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'university_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'study_program_name' => 'nullable|string|max:255',
            'gender' => 'nullable|string|in:male,female,other',
            'birth_date' => 'nullable|date|before:today',
        ]);

        $member->update($validated);

        return back()->with('success', 'Profile information updated successfully.');
    }
}
