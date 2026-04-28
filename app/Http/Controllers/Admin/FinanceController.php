<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashBookEntry;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $entries = CashBookEntry::latest()->paginate(20);
        return view('admin.finance.index', compact('entries'));
    }

    public function show(CashBookEntry $entry)
    {
        return view('admin.finance.show', compact('entry'));
    }

    public function destroy(CashBookEntry $entry)
    {
        $entry->delete();
        return redirect()->route('admin.finance.index')->with('success', 'Entry deleted');
    }
}
