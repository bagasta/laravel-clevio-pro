<?php
namespace App\Http\Controllers;

use App\Models\PrismaUser;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $loginUser = $request->user();

        // Fetch the Prisma user record (and related agents) by matching email
        $prismaUser = PrismaUser::with('agents')
            ->where('email', $loginUser->email)
            ->first();

        // Agents owned by the authenticated user
        $agents = $prismaUser?->agents?->sortByDesc('createdAt') ?? collect();

        return view('dashboard', compact('agents'));
    }
}
