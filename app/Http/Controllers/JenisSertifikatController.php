<?php

namespace App\Http\Controllers;

use App\Models\JenisSertifikat;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class JenisSertifikatController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['all', 'active', 'inactive'])],
            'sort' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', Rule::in(['10', '25', '50'])],
            'edit' => ['nullable', 'integer', Rule::exists('jenis_sertifikat', 'id_jenis')],
        ]);

        $search = trim($validated['search'] ?? '');
        $status = $validated['status'] ?? 'all';
        $sort = $validated['sort'] ?? 'asc';
        $perPage = (int) ($validated['per_page'] ?? 10);

        $jenisSertifikat = JenisSertifikat::query()
            ->withCount([
                'sertifikat',
            ])
            ->with([
                'template' => fn ($query) => $query
                    ->where('aktif', true)
                    ->select('id_template', 'id_jenis', 'nama_template'),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('kode', 'like', "%{$search}%")
                        ->orWhere('nama_jenis', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('aktif', true))
            ->when($status === 'inactive', fn ($query) => $query->where('aktif', false))
            ->orderBy('nama_jenis', $sort)
            ->paginate($perPage)
            ->withQueryString();

        if ($request->boolean('partial')) {
            return response()->json([
                'table' => view('components.admin.certificate-type-table', [
                    'jenisSertifikat' => $jenisSertifikat,
                ])->render(),
                'footer' => view('certificate-types.partials.pagination', [
                    'jenisSertifikat' => $jenisSertifikat,
                ])->render(),
                'summary' => sprintf(
                    'Menampilkan %d dari %d jenis sertifikat',
                    $jenisSertifikat->count(),
                    $jenisSertifikat->total(),
                ),
            ]);
        }

        $editJenis = isset($validated['edit'])
            ? JenisSertifikat::query()->findOrFail($validated['edit'])
            : null;
        $certificateTypeSuggestions = JenisSertifikat::query()
            ->select('nama_jenis')
            ->distinct()
            ->orderBy('nama_jenis')
            ->pluck('nama_jenis');

        return view('certificate-types.index', compact(
            'jenisSertifikat',
            'certificateTypeSuggestions',
            'search',
            'status',
            'sort',
            'perPage',
            'editJenis',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        JenisSertifikat::query()->create($validated);

        return redirect()->route('certificate-types.index')
            ->with('success', 'Jenis sertifikat berhasil ditambahkan.');
    }

    public function update(Request $request, JenisSertifikat $jenis): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules($jenis));

        if ($validator->fails()) {
            return redirect()
                ->route('certificate-types.index', ['edit' => $jenis->getKey()])
                ->withErrors($validator)
                ->withInput();
        }

        $jenis->update($validator->validated());

        return redirect()->route('certificate-types.index')
            ->with('success', 'Jenis sertifikat berhasil diperbarui.');
    }

    public function toggle(JenisSertifikat $jenis): RedirectResponse
    {
        $jenis->update(['aktif' => ! $jenis->aktif]);
        $message = $jenis->aktif
            ? 'Jenis sertifikat berhasil diaktifkan.'
            : 'Jenis sertifikat berhasil dinonaktifkan.';

        return redirect()->route('certificate-types.index')->with('success', $message);
    }

    public function destroy(JenisSertifikat $jenis): RedirectResponse
    {
        if ($jenis->template()->exists() || $jenis->sertifikat()->exists() || $jenis->nomorUrut()->exists()) {
            return redirect()->route('certificate-types.index')
                ->with('error', 'Jenis sertifikat tidak dapat dihapus karena masih digunakan oleh template, sertifikat, atau nomor urut.');
        }

        $jenis->delete();

        return redirect()->route('certificate-types.index')
            ->with('success', 'Jenis sertifikat berhasil dihapus.');
    }

    private function rules(?JenisSertifikat $jenis = null): array
    {
        return [
            'kode' => [
                'required',
                'string',
                'min:2',
                'max:10',
                'alpha_num',
                Rule::unique('jenis_sertifikat', 'kode')->ignore($jenis?->getKey(), 'id_jenis'),
                ...($jenis && $jenis->sertifikat()->exists() ? [Rule::in([$jenis->kode])] : []),
            ],
            'nama_jenis' => ['required', 'string', 'max:100'],
            'aktif' => ['required', 'boolean'],
        ];
    }
}
