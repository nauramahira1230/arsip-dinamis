<?php

namespace App\Controllers;

use App\Models\KegiatanModel;

class KegiatanController extends BaseController
{
    protected $kegiatanModel;

    public function __construct()
    {
        $this->kegiatanModel = new KegiatanModel();
    }

    public function index()
    {
        return view('kegiatan/index', ['kegiatan' => $this->kegiatanModel->getForImport()]);
    }

    public function create()
    {
        return view('kegiatan/form');
    }

    public function store()
    {
        $data = $this->validatedInput();
        if ($data === null) {
            return redirect()->back()->withInput()->with('error', 'Lengkapi data kegiatan dengan benar.');
        }

        $this->kegiatanModel->insert($data);
        return redirect()->to('/kegiatan')->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $activity = $this->kegiatanModel->find($id);
        if (!$activity) {
            return redirect()->to('/kegiatan')->with('error', 'Kegiatan tidak ditemukan.');
        }

        return view('kegiatan/form', ['kegiatan' => $activity]);
    }

    public function update(string $id)
    {
        if (!$this->kegiatanModel->find($id)) {
            return redirect()->to('/kegiatan')->with('error', 'Kegiatan tidak ditemukan.');
        }

        $data = $this->validatedInput();
        if ($data === null) {
            return redirect()->back()->withInput()->with('error', 'Lengkapi data kegiatan dengan benar.');
        }

        $this->kegiatanModel->update($id, $data);
        return redirect()->to('/kegiatan')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function delete(string $id)
    {
        $this->kegiatanModel->delete($id);
        return redirect()->to('/kegiatan')->with('success', 'Kegiatan berhasil dihapus.');
    }

    private function validatedInput(): ?array
    {
        $nama = trim((string) $this->request->getPost('nama_kegiatan'));
        $tahun = (int) $this->request->getPost('tahun');
        $jenis = trim((string) $this->request->getPost('jenis_naskah'));
        $target = (int) $this->request->getPost('target_lembar');

        if ($nama === '' || $tahun < 1 || $jenis === '' || $target < 1) {
            return null;
        }

        return [
            'nama_kegiatan' => $nama,
            'tahun' => $tahun,
            'jenis_naskah' => $jenis,
            'target_lembar' => $target,
        ];
    }
}