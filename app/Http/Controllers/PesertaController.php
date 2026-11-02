<?php

namespace App\Http\Controllers;

use App\Imports\PesertaImport;
use App\Models\Peserta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class PesertaController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'per_page' => ['nullable', Rule::in(['10', '25', '50'])],
            'sort' => ['nullable', Rule::in(['newest', 'oldest'])],
            'edit' => ['nullable', 'integer', Rule::exists('peserta', 'id_peserta')],
        ]);
        $search = trim($validated['search'] ?? '');
        $perPage = (int) ($validated['per_page'] ?? 10);
        $sort = $validated['sort'] ?? 'newest';

        $participants = Peserta::query()
            ->withCount('sertifikat')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nama', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('asal_instansi', 'like', "%{$search}%");
                });
            })
            ->orderBy('dibuat_pada', $sort === 'oldest' ? 'asc' : 'desc')
            ->orderBy('id_peserta', $sort === 'oldest' ? 'asc' : 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $editParticipant = isset($validated['edit'])
            ? Peserta::query()->findOrFail($validated['edit'])
            : null;

        return view('participants.index', compact('participants', 'search', 'perPage', 'sort', 'editParticipant'));
    }

    public function create(): View
    {
        return view('participants.create', ['participant' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->participantRules());
        Peserta::query()->create($validated);

        return redirect()->route('participants.index')->with('success', 'Data peserta berhasil ditambahkan.');
    }

    public function show(Peserta $peserta): View
    {
        $peserta->loadCount('sertifikat')
            ->load([
                'sertifikat' => fn ($query) => $query
                    ->with('jenis:id_jenis,nama_jenis')
                    ->orderByDesc('tanggal_terbit'),
            ]);

        return view('participants.show', compact('peserta'));
    }

    public function edit(Peserta $peserta): View
    {
        return view('participants.edit', ['participant' => $peserta]);
    }

    public function update(Request $request, Peserta $peserta): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->participantRules());

        if ($validator->fails()) {
            return redirect()
                ->route('participants.index', ['edit' => $peserta->getKey()])
                ->withErrors($validator)
                ->withInput();
        }

        $peserta->update($validator->validated());

        return redirect()->route('participants.index')->with('success', 'Data peserta berhasil diperbarui.');
    }

    public function destroy(Peserta $peserta): RedirectResponse
    {
        if ($peserta->sertifikat()->exists()) {
            return redirect()->route('participants.index')
                ->with('error', 'Peserta tidak dapat dihapus karena masih memiliki sertifikat.');
        }

        $peserta->delete();

        return redirect()->route('participants.index')->with('success', 'Data peserta berhasil dihapus.');
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $import = new PesertaImport();
        Excel::import($import, $validated['file']);

        return redirect()->route('participants.index')
            ->with('success', "{$import->importedCount()} data peserta berhasil diimpor.");
    }

    private function participantRules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150'],
            'asal_instansi' => ['nullable', 'string', 'max:150'],
        ];
    }
}
