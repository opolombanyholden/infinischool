<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:student']);
    }

    public function index()
    {
        $student = auth()->user();

        $certificates = Certificate::byStudent($student->id)
            ->with(['formation', 'enrollment'])
            ->latest('issued_at')
            ->get();

        $stats = [
            'total_certificates' => $certificates->count(),
            'verified_certificates' => $certificates->where('is_verified', true)->count(),
            'active_certificates' => $certificates->filter(fn($c) => $c->isActive())->count(),
        ];

        return view('student.certificates.index', compact('certificates', 'stats'));
    }

    public function show($id)
    {
        $certificate = Certificate::byStudent(auth()->id())
            ->with(['formation', 'enrollment'])
            ->findOrFail($id);

        return view('student.certificates.show', compact('certificate'));
    }

    public function download($id)
    {
        $certificate = Certificate::byStudent(auth()->id())->findOrFail($id);

        if (!$certificate->file_path) {
            return back()->with('error', 'Le certificat n\'est pas encore disponible au téléchargement.');
        }

        return Storage::disk('public')->download(
            $certificate->file_path,
            "certificat_{$certificate->certificate_number}.pdf"
        );
    }

    public function verify($code)
    {
        $certificate = Certificate::verifyByCode($code);

        if (!$certificate) {
            return view('certificates.verify', [
                'verified' => false,
                'message' => 'Code de vérification invalide.'
            ]);
        }

        return view('certificates.verify', [
            'verified' => true,
            'certificate' => $certificate,
        ]);
    }
}