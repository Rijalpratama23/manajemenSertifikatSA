<?php

namespace App\Http\Controllers;

use App\Imports\PesertaImport;
use App\Models\JenisSertifikat;
use App\Models\Peserta;
use App\Services\CourseParticipantSyncService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PesertaController extends Controller
{
    public function index(Request $request, CourseParticipantSyncService $courseParticipantSync): View|JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'jenis' => ['nullable', 'integer', Rule::exists('jenis_sertifikat', 'id_jenis')],
            'per_page' => ['nullable', Rule::in(['10', '25', '50'])],
            'sort' => ['nullable', Rule::in(['newest', 'oldest'])],
            'edit' => ['nullable', 'integer', Rule::exists('peserta', 'id_peserta')],
        ]);
        $search = trim($validated['search'] ?? '');
        $selectedType = (string) ($validated['jenis'] ?? '');
        $perPage = (int) ($validated['per_page'] ?? 10);
        $sort = $validated['sort'] ?? 'newest';
        $isCourseView = false;

        $selectedTypeCode = $selectedType !== ''
            ? JenisSertifikat::query()->whereKey((int) $selectedType)->value('kode')
            : null;

        if (in_array($selectedTypeCode, ['KURSUS', '34'], true)) {
            $isCourseView = true;
            $courseParticipantSync->sync();
            $participants = Peserta::query()
                ->whereNotNull('nis')
                ->withCount('sertifikat')
                ->with(['sertifikat:id_sertifikat,id_peserta,id_jenis,status'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('nama', 'like', "%{$search}%")
                            ->orWhere('nis', 'like', "%{$search}%");
                    });
                })
                ->orderBy('nama', $sort === 'oldest' ? 'asc' : 'desc')
                ->orderBy('id_peserta', $sort === 'oldest' ? 'asc' : 'desc')
                ->paginate($perPage)
                ->withQueryString();

            $participants->getCollection()->transform(function ($course) {
                $course->source = 'peserta-kursus';
                return $course;
            });
            $editParticipant = isset($validated['edit'])
                ? Peserta::query()->findOrFail($validated['edit'])
                : null;
            if ($editParticipant) {
                $editParticipant->setAttribute('source', 'peserta-kursus');
            }
        } elseif ($selectedType === '') {
            $courseParticipants = Schema::hasTable('kursus')
                ? DB::table('kursus')->select(['id', 'nis', 'nama'])->get()
                : collect();

            $regularParticipants = Peserta::query()
                ->withCount('sertifikat')
                ->get();

            $combined = collect();

            foreach ($regularParticipants as $participant) {
                $participant->setAttribute('nis', null);
                $participant->setAttribute('source', 'peserta');
                $combined->put('peserta:'.strtolower(trim((string) $participant->nama)), $participant);
            }

            foreach ($courseParticipants as $course) {
                $key = 'peserta:'.strtolower(trim((string) $course->nama));

                if ($combined->has($key)) {
                    $participant = $combined->get($key);
                    $participant->setAttribute('nis', $course->nis);
                    $participant->setAttribute('source', 'peserta-kursus');
                    $combined->put($key, $participant);
                } else {
                    $course->id_peserta = null;
                    $course->email = null;
                    $course->asal_instansi = null;
                    $course->sertifikat_count = 0;
                    $course->dibuat_pada = null;
                    $course->source = 'kursus';
                    $combined->put('kursus:'.$course->id, $course);
                }
            }

            $combined = $combined->values();

            if ($search !== '') {
                $needle = strtolower($search);
                $combined = $combined->filter(function ($participant) use ($needle) {
                    return str_contains(strtolower((string) $participant->nama), $needle)
                        || str_contains(strtolower((string) $participant->email), $needle)
                        || str_contains(strtolower((string) $participant->asal_instansi), $needle)
                        || str_contains(strtolower((string) $participant->nis), $needle);
                })->values();
            }

            $combined = $sort === 'oldest'
                ? $combined->sortBy(fn ($participant) =>
                    $participant->dibuat_pada?->getTimestamp()
                    ?? (is_numeric($participant->nis) ? (int) $participant->nis : PHP_INT_MAX)
                )->values()
                : $combined->sortByDesc(fn ($participant) =>
                    $participant->dibuat_pada?->getTimestamp()
                    ?? (is_numeric($participant->nis) ? (int) $participant->nis : 0)
                )->values();

            $page = max(1, (int) $request->integer('page', 1));
            $participants = new LengthAwarePaginator(
                $combined->slice(($page - 1) * $perPage, $perPage)->values(),
                $combined->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()],
            );
            $editParticipant = isset($validated['edit'])
                ? Peserta::query()->findOrFail($validated['edit'])
                : null;
        } else {
            $participants = Peserta::query()
                ->withCount('sertifikat')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('nama', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('asal_instansi', 'like', "%{$search}%");
                    });
                })
                ->when($selectedType !== '', fn ($query) => $query->whereHas(
                    'sertifikat',
                    fn ($certificateQuery) => $certificateQuery->where('id_jenis', $selectedType),
                ))
                ->orderBy('dibuat_pada', $sort === 'oldest' ? 'asc' : 'desc')
                ->orderBy('id_peserta', $sort === 'oldest' ? 'asc' : 'desc')
                ->paginate($perPage)
                ->withQueryString();

            $courseNisByName = Schema::hasTable('kursus')
                ? DB::table('kursus')
                    ->select(['nama', 'nis'])
                    ->get()
                    ->keyBy(fn ($course) => strtolower(trim((string) $course->nama)))
                : collect();

            $participants->getCollection()->transform(function (Peserta $participant) use ($courseNisByName) {
                $course = $courseNisByName->get(strtolower(trim((string) $participant->nama)));
                $participant->setAttribute('nis', $course?->nis);
                $participant->setAttribute('source', 'peserta');
                return $participant;
            });

            $editParticipant = isset($validated['edit'])
                ? Peserta::query()->findOrFail($validated['edit'])
                : null;
        }

        $certificateTypes = JenisSertifikat::query()
            ->where('aktif', true)
            ->whereIn('kode', ['KURSUS', '34', 'PKL', 'MAGANG', 'UJIKOM', 'LPK'])
            ->get(['id_jenis', 'kode', 'nama_jenis'])
            ->sortBy(fn (JenisSertifikat $certificateType) =>
                ['KURSUS' => 1, '34' => 1, 'PKL' => 2, 'MAGANG' => 3, 'UJIKOM' => 4, 'LPK' => 5][$certificateType->kode]
            )
            ->values();
        $selectedCertificateTypeName = $selectedType !== ''
            ? ($certificateTypes->firstWhere('id_jenis', (int) $selectedType)?->nama_jenis)
            : null;

        if ($request->boolean('partial')) {
            return response()->json([
                'table' => view('components.admin.participant-table', [
                    'participants' => $participants,
                    'isCourseView' => $isCourseView,
                ])->render(),
                'footer' => view('participants.partials.pagination', [
                    'participants' => $participants,
                ])->render(),
                'summary' => sprintf(
                    'Menampilkan %d dari %d peserta',
                    $participants->count(),
                    $participants->total(),
                ),
            ]);
        }

        $participantSearchSuggestions = Peserta::query()
            ->orderBy('nama')
            ->pluck('nama');
        if (Schema::hasTable('kursus')) {
            $participantSearchSuggestions = $participantSearchSuggestions->merge(
                DB::table('kursus')->orderBy('nama')->pluck('nama'),
            );
        }
        $participantSearchSuggestions = $participantSearchSuggestions
            ->filter(fn ($name) => is_string($name) && trim($name) !== '')
            ->unique(fn ($name) => mb_strtolower(trim($name)))
            ->values();

        return view('participants.index', compact(
            'participants',
            'certificateTypes',
            'participantSearchSuggestions',
            'selectedCertificateTypeName',
            'search',
            'selectedType',
            'perPage',
            'sort',
            'editParticipant',
            'isCourseView',
        ));
    }

    public function create(): View
    {
        return view('participants.create', ['participant' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $typeId = $request->input('jenis');
        $typeId = is_scalar($typeId) && is_numeric($typeId) ? (int) $typeId : null;
        $type = $typeId !== null
            ? JenisSertifikat::query()->find($typeId, ['id_jenis', 'kode'])
            : null;
        $isCourseContext = $type && in_array($type->kode, ['KURSUS', '34'], true);
        $validated = $request->validate([
            ...$this->participantRules(null, $isCourseContext),
            'jenis' => ['nullable', 'integer', Rule::exists('jenis_sertifikat', 'id_jenis')],
        ]);
        $selectedType = $validated['jenis'] ?? null;
        unset($validated['jenis']);
        Peserta::query()->create($validated);

        return redirect()->route('participants.index', $selectedType ? ['jenis' => $selectedType] : [])
            ->with('success', 'Data peserta berhasil ditambahkan.');
    }

    public function show(Peserta $peserta): View
    {
        $peserta->load([
            'sertifikat' => fn ($query) => $query
                ->with('jenis:id_jenis,nama_jenis')
                ->orderByDesc('tanggal_terbit'),
        ]);
        $peserta->setAttribute('sertifikat_count', $peserta->sertifikat->count());

        return view('participants.show', compact('peserta'));
    }

    public function edit(Peserta $peserta): View
    {
        return view('participants.edit', ['participant' => $peserta]);
    }

    public function update(Request $request, Peserta $peserta): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->participantRules($peserta));

        if ($validator->fails()) {
            return redirect()
                ->route('participants.index', ['edit' => $peserta->getKey()])
                ->withErrors($validator)
                ->withInput();
        }

        $attributes = $validator->validated();
        $previousNis = $peserta->nis;
        DB::transaction(function () use ($peserta, $attributes, $previousNis) {
            if ($previousNis !== null && Schema::hasTable('kursus')) {
                DB::table('kursus')
                    ->where('nis', $previousNis)
                    ->update([
                        'nis' => $attributes['nis'],
                        'nama' => $attributes['nama'],
                    ]);
            }

            $peserta->update($attributes);
        });

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

    public function downloadTemplate(): BinaryFileResponse
    {
        return response()->download(
            resource_path('templates/template_data_peserta.xlsx'),
            'template_data_peserta.xlsx',
        );
    }

    private function participantRules(?Peserta $participant = null, bool $courseContext = false): array
    {
        $nisRule = Rule::unique('peserta', 'nis');
        if ($participant) {
            $nisRule->ignore($participant->getKey(), 'id_peserta');
        }

        return [
            'nama' => ['required', 'string', 'max:150'],
            'nis' => [
                $courseContext || $participant?->nis !== null ? 'required' : 'nullable',
                'integer',
                'min:1',
                $nisRule,
            ],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'email' => ['nullable', 'string', 'email', 'max:150'],
            'asal_instansi' => ['nullable', 'string', 'max:150'],
        ];
    }
}
