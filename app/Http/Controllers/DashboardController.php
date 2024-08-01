<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreTopupWalletRequest;
use App\Http\Requests\StoreWithdrawalWalletRequest;
use App\Models\Project;
use App\Models\ProjectApplicant;

class DashboardController extends Controller
{
    public function proposals()
    {
        return view('dashboard.proposals');
    }

    public function proposal_details(Project $project, ProjectApplicant $projectApplicant)
    {
        if($projectApplicant->freelancer_id != auth()->id()){
            return abort(403, 'You arer not authorized to see this page');
        }

        return view('dashboard.proposal_details', compact('project', 'projectApplicant'));
    }

    public function wallet()
    {
        $user = Auth::user();

        $wallet_transactions = WalletTransaction::where('user_id', $user->id)
            ->orderByDesc('id')
            ->paginate(10);

        return view('dashboard.wallet', compact('wallet_transactions'));
    }

    public function withdraw_wallet()
    {
        return view('dashboard.withdraw_wallet');
    }

    public function withdraw_wallet_store(StoreWithdrawalWalletRequest $request)
    {
        $user = Auth::user();

        if ($user->wallet->balance < 100000) {
            return redirect()->back()->withErrors([
                'amount' => 'Balance Anda saat ini tidak cukup',
            ]);
        }

        DB::transaction(function () use ($request, $user) {
            $validated = $request->validated();

            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('proofs', 'public');
                $validated['proof'] = $proofPath;
            }

            $validated['type'] = 'Withdraw';
            $validated['amount'] = $user->wallet->balance;
            $validated['is_paid'] = false;
            $validated['user_id'] = $user->id;

            $newTopupWallet = WalletTransaction::create($validated);

            $user->wallet->update([
                'balance' => 0
            ]);
        });

        return redirect()->route('dashboard.wallet');
    }

    public function topup_wallet()
    {
        return view('dashboard.topup_wallet');
    }

    public function topup_wallet_store(StoreTopupWalletRequest $request)
    {
        $user = Auth::user();

        DB::transaction(function () use ($request, $user) {
            $validated = $request->validated();

            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('proofs', 'public');
                $validated['proof'] = $proofPath;
            }

            $validated['type'] = 'Topup';
            $validated['is_paid'] = false;
            $validated['user_id'] = $user->id;

            $newTopupWallet = WalletTransaction::create($validated);
        });

        return redirect()->route('dashboard.wallet');
    }
}
