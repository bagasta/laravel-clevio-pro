<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agent;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Ambil Agent milik user dari DB Prisma (kolom ownerId -> User.id (uuid string))
        $agents = Agent::query()
            ->where('ownerId', $user->id)
            ->orderBy('createdAt', 'desc')
            ->get();

        return view('dashboard', compact('agents'));
    }
}
