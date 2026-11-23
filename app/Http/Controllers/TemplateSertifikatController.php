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
        $positionConfig = $this->defaultPositionConfig();
        $positionElements = $this->positionElements();

        return view('certificate-templates.form', compact(
            'jenisSertifikat',
            'editTemplate',
            'activeTemplates',
            'selectedType',
            'positionConfig',
            'positionElements',
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
        $positionConfig = array_replace_recursive(
            $this->defaultPositionConfig(),
            (array) $template->konfigurasi_posisi,
        );
        $positionElements = $this->positionElements();

        return view('certificate-templates.form', compact(
            'jenisSertifikat',
            'editTemplate',
            'activeTemplates',
            'selectedType',
            'positionConfig',
            'positionElements',
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

        DB::transaction(function () use ($validated, $path, $request) {
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
                'konfigurasi_posisi' => $this->normalizePositionConfig($request->input('posisi')),
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

        $validated['konfigurasi_posisi'] = $this->normalizePositionConfig($request->input('posisi'));

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
                'dimensions:min_width=1403,min_height=992,max_ratio=1',
                'max:5120',
            ],
            'aktif' => ['required', 'boolean'],
            'posisi' => ['nullable', 'array'],
            'posisi.nama.x' => ['nullable', 'numeric', 'between:0,100'],
            'posisi.nama.y' => ['nullable', 'numeric', 'between:0,100'],
            'posisi.nama.size' => ['nullable', 'numeric', 'between:6,60'],
            'posisi.nomor.x' => ['nullable', 'numeric', 'between:0,100'],
            'posisi.nomor.y' => ['nullable', 'numeric', 'between:0,100'],
            'posisi.nomor.size' => ['nullable', 'numeric', 'between:6,40'],
            'posisi.deskripsi.x' => ['nullable', 'numeric', 'between:0,100'],
            'posisi.deskripsi.y' => ['nullable', 'numeric', 'between:0,100'],
            'posisi.deskripsi.size' => ['nullable', 'numeric', 'between:6,40'],
            'posisi.tanggal.x' => ['nullable', 'numeric', 'between:0,100'],
            'posisi.tanggal.y' => ['nullable', 'numeric', 'between:0,100'],
            'posisi.tanggal.size' => ['nullable', 'numeric', 'between:6,40'],
            'posisi.qr.x' => ['nullable', 'numeric', 'between:0,100'],
            'posisi.qr.y' => ['nullable', 'numeric', 'between:0,100'],
            'posisi.qr.size' => ['nullable', 'numeric', 'between:5,40'],
            'posisi.diberikan.x' => ['nullable', 'numeric', 'between:0,100'],
            'posisi.diberikan.y' => ['nullable', 'numeric', 'between:0,100'],
            'posisi.diberikan.size' => ['nullable', 'numeric', 'between:6,40'],
            'posisi.judul.x' => ['nullable', 'numeric', 'between:0,100'],
            'posisi.judul.y' => ['nullable', 'numeric', 'between:0,100'],
            'posisi.judul.size' => ['nullable', 'numeric', 'between:6,60'],
        ];
    }

    private function activeTemplates(): Collection
    {
        return TemplateSertifikat::query()
            ->where('aktif', true)
            ->with('jenis:id_jenis,nama_jenis')
            ->get(['id_template', 'id_jenis', 'nama_template']);
    }

    private function defaultPositionConfig(): array
    {
        return [
            'nama' => ['x' => 50, 'y' => 40, 'size' => 25],
            'nomor' => ['x' => 22, 'y' => 86, 'size' => 11],
            'deskripsi' => ['x' => 50, 'y' => 58, 'size' => 15],
            'tanggal' => ['x' => 50, 'y' => 76, 'size' => 13],
            'qr' => ['x' => 82, 'y' => 86, 'size' => 12],
            'diberikan' => ['x' => 50, 'y' => 31, 'size' => 13],
            'judul' => ['x' => 50, 'y' => 17, 'size' => 30],
        ];
    }

    private function normalizePositionConfig(mixed $position): array
    {
        $defaults = $this->defaultPositionConfig();

        if (! is_array($position)) {
            return $defaults;
        }

        foreach ($defaults as $key => $default) {
            $defaults[$key] = [
                'x' => isset($position[$key]['x']) ? (float) $position[$key]['x'] : $default['x'],
                'y' => isset($position[$key]['y']) ? (float) $position[$key]['y'] : $default['y'],
                'size' => isset($position[$key]['size']) ? (float) $position[$key]['size'] : $default['size'],
            ];
        }

        return $defaults;
    }

    private function positionElements(): array
    {
        return [
            'judul' => ['label' => 'Judul Sertifikat', 'color' => 'teal'],
            'diberikan' => ['label' => 'Diberikan Kepada', 'color' => 'cyan'],
            'nama' => ['label' => 'Nama Peserta', 'color' => 'gold'],
            'nomor' => ['label' => 'Nomor Sertifikat', 'color' => 'green'],
            'deskripsi' => ['label' => 'Deskripsi', 'color' => 'blue'],
            'tanggal' => ['label' => 'Tanggal / Periode', 'color' => 'purple'],
            'qr' => ['label' => 'QR Verifikasi', 'color' => 'red'],
        ];
    }
}
