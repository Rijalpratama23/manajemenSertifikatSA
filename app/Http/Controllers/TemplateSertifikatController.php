<?php

namespace App\Http\Controllers;

use App\Models\JenisSertifikat;
use App\Models\TemplateSertifikat;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class TemplateSertifikatController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['all', 'active', 'inactive'])],
            'jenis' => ['nullable', 'integer', Rule::exists('jenis_sertifikat', 'id_jenis')],
            'per_page' => ['nullable', Rule::in(['6', '9', '12'])],
        ]);

        $search = trim($validated['search'] ?? '');
        $status = $validated['status'] ?? 'all';
        $jenisId = $validated['jenis'] ?? '';
        $perPage = (int) ($validated['per_page'] ?? 9);

        $templates = TemplateSertifikat::query()
            ->with(['jenis:id_jenis,nama_jenis,kode'])
            ->withCount('sertifikat')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nama_template', 'like', "%{$search}%")
                        ->orWhereHas('jenis', fn ($jenisQuery) => $jenisQuery
                            ->where('nama_jenis', 'like', "%{$search}%")
                            ->orWhere('kode', 'like', "%{$search}%"));
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('aktif', true))
            ->when($status === 'inactive', fn ($query) => $query->where('aktif', false))
            ->when($jenisId !== '', fn ($query) => $query->where('id_jenis', $jenisId))
            ->orderByDesc('dibuat_pada')
            ->orderByDesc('id_template')
            ->paginate($perPage)
            ->withQueryString();

        $jenisSertifikat = JenisSertifikat::query()
            ->orderBy('nama_jenis')
            ->get(['id_jenis', 'nama_jenis', 'kode', 'aktif']);

        $activeTemplates = TemplateSertifikat::query()
            ->where('aktif', true)
            ->with('jenis:id_jenis,nama_jenis')
            ->get(['id_template', 'id_jenis', 'nama_template']);

        $jenisTanpaTemplateAktif = JenisSertifikat::query()
            ->where('aktif', true)
            ->whereDoesntHave('template', fn ($query) => $query->where('aktif', true))
            ->orderBy('nama_jenis')
            ->get(['id_jenis', 'nama_jenis']);

        return view('certificate-templates.index', compact(
            'templates',
            'jenisSertifikat',
            'activeTemplates',
            'jenisTanpaTemplateAktif',
            'search',
            'status',
            'jenisId',
            'perPage',
        ));
    }

    public function create(Request $request): View
    {
        $validated = $request->validate([
            'jenis' => ['nullable', 'integer', Rule::exists('jenis_sertifikat', 'id_jenis')->where('aktif', true)],
        ]);
        $jenisSertifikat = JenisSertifikat::query()
            ->where('aktif', true)
            ->orderBy('nama_jenis')
            ->get(['id_jenis', 'nama_jenis', 'kode']);
        $editTemplate = null;
        $activeTemplates = $this->activeTemplates();
        $selectedType = $validated['jenis'] ?? null;

        return view('certificate-templates.form', compact(
            'jenisSertifikat',
            'editTemplate',
            'activeTemplates',
            'selectedType',
        ));
    }

    public function edit(TemplateSertifikat $template): View
    {
        $jenisSertifikat = JenisSertifikat::query()
            ->where(function ($query) use ($template) {
                $query->where('aktif', true);
                $query->orWhere('id_jenis', $template->id_jenis);
            })
            ->orderBy('nama_jenis')
            ->get(['id_jenis', 'nama_jenis', 'kode', 'aktif']);
        $editTemplate = $template;
        $activeTemplates = $this->activeTemplates();
        $selectedType = $template->id_jenis;

        return view('certificate-templates.form', compact(
            'jenisSertifikat',
            'editTemplate',
            'activeTemplates',
            'selectedType',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $file = $request->file('berkas_desain');
        $path = $file->store('certificate-templates', 'local');

        if ($path === false) {
            throw new RuntimeException('Berkas template gagal disimpan.');
        }

        DB::transaction(function () use ($validated, $path) {
            if ($validated['aktif']) {
                TemplateSertifikat::query()
                    ->where('id_jenis', $validated['id_jenis'])
                    ->where('aktif', true)
                    ->update(['aktif' => false]);
            }

            TemplateSertifikat::query()->create([
                'id_jenis' => $validated['id_jenis'],
                'nama_template' => $validated['nama_template'],
                'berkas_desain' => $path,
                'aktif' => $validated['aktif'],
            ]);
        });

        return redirect()->route('certificate-templates.index')
            ->with('success', 'Template sertifikat berhasil ditambahkan.');
    }

    public function update(Request $request, TemplateSertifikat $template): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules($template));

        if ($validator->fails()) {
            return redirect()
                ->route('certificate-templates.edit', $template)
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $oldPath = $template->berkas_desain;

        if ($request->hasFile('berkas_desain')) {
            $path = $request->file('berkas_desain')->store('certificate-templates', 'local');

            if ($path === false) {
                throw new RuntimeException('Berkas template gagal disimpan.');
            }

            $validated['berkas_desain'] = $path;
        } else {
            unset($validated['berkas_desain']);
        }

        DB::transaction(function () use ($template, $validated) {
            if (($validated['aktif'] ?? $template->aktif) && isset($validated['id_jenis'])) {
                TemplateSertifikat::query()
                    ->where('id_jenis', $validated['id_jenis'])
                    ->where('aktif', true)
                    ->where('id_template', '!=', $template->getKey())
                    ->update(['aktif' => false]);
            }

            $template->update($validated);
        });

        if (isset($validated['berkas_desain']) && $oldPath !== $template->berkas_desain) {
            if (! Storage::disk('local')->delete($oldPath)) {
                return redirect()->route('certificate-templates.index')
                    ->with('error', 'Template berhasil diperbarui, tetapi berkas desain lama gagal dihapus.');
            }
        }

        return redirect()->route('certificate-templates.index')
            ->with('success', 'Template sertifikat berhasil diperbarui.');
    }

    public function toggle(TemplateSertifikat $template): RedirectResponse
    {
        $nextStatus = ! $template->aktif;

        DB::transaction(function () use ($template, $nextStatus) {
            if ($nextStatus) {
                TemplateSertifikat::query()
                    ->where('id_jenis', $template->id_jenis)
                    ->where('aktif', true)
                    ->where('id_template', '!=', $template->getKey())
                    ->update(['aktif' => false]);
            }

            $template->update(['aktif' => $nextStatus]);
        });
        $message = $template->aktif
            ? 'Template sertifikat berhasil diaktifkan.'
            : 'Template sertifikat berhasil dinonaktifkan.';

        return redirect()->route('certificate-templates.index')->with('success', $message);
    }

    public function preview(TemplateSertifikat $template)
    {
        $disk = Storage::disk('local');
        abort_unless($disk->exists($template->berkas_desain), 404);

        return response()->file($disk->path($template->berkas_desain));
    }

    public function destroy(TemplateSertifikat $template): RedirectResponse
    {
        if ($template->sertifikat()->exists()) {
            return redirect()->route('certificate-templates.index')
                ->with('error', 'Template tidak dapat dihapus karena sudah digunakan oleh sertifikat.');
        }

        $path = $template->berkas_desain;
        $template->delete();

        if (! Storage::disk('local')->delete($path)) {
            return redirect()->route('certificate-templates.index')
                ->with('error', 'Template telah dihapus, tetapi berkas desainnya gagal dihapus dari penyimpanan.');
        }

        return redirect()->route('certificate-templates.index')
            ->with('success', 'Template sertifikat berhasil dihapus.');
    }

    private function rules(?TemplateSertifikat $template = null): array
    {
        return [
            'id_jenis' => [
                'required',
                'integer',
                $template && (
                    $template->sertifikat()->exists()
                    || ! $template->jenis()->where('aktif', true)->exists()
                )
                    ? Rule::in([$template->id_jenis])
                    : Rule::exists('jenis_sertifikat', 'id_jenis')->where('aktif', true),
            ],
            'nama_template' => ['required', 'string', 'max:100'],
            'berkas_desain' => [
                $template ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png',
                'dimensions:min_width=1754,min_height=1240,min_ratio=1',
                'max:5120',
            ],
            'aktif' => ['required', 'boolean'],
        ];
    }

    private function activeTemplates(): Collection
    {
        return TemplateSertifikat::query()
            ->where('aktif', true)
            ->with('jenis:id_jenis,nama_jenis')
            ->get(['id_template', 'id_jenis', 'nama_template']);
    }
}
