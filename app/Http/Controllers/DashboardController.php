<?php
namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\PrismaUser;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $loginUser = $request->user();

        // Cari id user di tabel Prisma berdasarkan email login
        $prismaUserId = PrismaUser::where('email', $loginUser->email)->value('id');

        // Ambil Agent milik user dari DB Prisma (kolom ownerId -> PrismaUser.id)
        $agents = Agent::query()
            ->where('ownerId', $prismaUserId)
            ->orderBy('createdAt', 'desc')
            ->get();

        return view('dashboard', compact('agents'));
    }
}
