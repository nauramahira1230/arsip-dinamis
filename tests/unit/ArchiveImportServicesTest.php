<?php

use App\Models\ArchiveModel;
use App\Services\ArchiveAIService;
use App\Services\ArchiveTransformService;
use App\Services\ExcelImportService;
use App\Services\NumberingService;
use CodeIgniter\Test\CIUnitTestCase;

final class ArchiveImportServicesTest extends CIUnitTestCase
{
    public function testHeadersKeepIndexSeparateFromClassification(): void
    {
        $mapping = (new ExcelImportService())->detectMapping(['NO', 'INDEKS', 'KLAS.', 'URAIAN', 'KURUN WAKTU', 'JUMLAH']);

        $this->assertSame(1, $mapping['indeks']);
        $this->assertSame(2, $mapping['kode_klasifikasi']);
        $this->assertSame(5, $mapping['jumlah_lembar']);
    }

    public function testNumberingUsesYearForCoverAndDoesNotCreateItemNumbers(): void
    {
        $rows = [
            ['kurun_waktu' => '1998', 'no_sampul' => null, 'no_item' => null],
            ['kurun_waktu' => '1998', 'no_sampul' => null, 'no_item' => 'A-7'],
            ['kurun_waktu' => '1999', 'no_sampul' => null, 'no_item' => null],
        ];

        $result = (new NumberingService(new ArchiveModel()))->apply($rows, 'kegiatan', 'new');

        $this->assertSame(1, $result[0]['no_sampul']);
        $this->assertSame(2, $result[1]['no_sampul']);
        $this->assertSame(1, $result[2]['no_sampul']);
        $this->assertNull($result[0]['no_item']);
        $this->assertSame('A-7', $result[1]['no_item']);
    }

    public function testRuleBasedAIUsesStatusInsteadOfVerificationText(): void
    {
        $result = (new ArchiveAIService())->analyze(['SALINAN SK GUBERNUR tentang HGB PT Contoh di Desa Sukamaju']);

        $this->assertSame('Salinan Keputusan', $result[0]['jenis_naskah']);
        $this->assertSame('HGB PT Contoh', $result[0]['nama_berkas']);
        $this->assertNotSame('SALINAN SK GUBERNUR tentang HGB PT Contoh di Desa Sukamaju', $result[0]['nama_berkas']);
        $this->assertNotSame('Perlu Verifikasi', $result[0]['jenis_naskah']);
        $this->assertSame('AUTO', $result[0]['ai_status']);
        $this->assertArrayHasKey('desa', $result[0]['ai_metadata']);
    }

    public function testFileNameSummarizesPermitSubjectAndApplicant(): void
    {
        $description = 'Surat Keputusan SK NO: 503.536/17/VII/1984 TENTANG IZIN TEMPAT USAHA PEMOHON SDR. DJOHAR TOBING A.N. PT. GOLDEN AGIN DI DESA MEKAR JAYA KEC. SUKMAJAYA';

        $result = (new ArchiveAIService())->analyze([$description]);

        $this->assertSame(
            'Perizinan TEMPAT USAHA SDR. DJOHAR TOBING A.N. PT. GOLDEN AGIN',
            $result[0]['nama_berkas']
        );
        $this->assertNotSame($description, $result[0]['nama_berkas']);
    }

    public function testTransformPreservesIndexAndAuthenticationDefaults(): void
    {
        $sheet = ['rows' => [['001', 'HGB', '593', 'SK GUBERNUR 12/ABC', '1998']]];
        $mapping = [
            'no' => 0,
            'indeks' => 1,
            'kode_klasifikasi' => 2,
            'uraian' => 3,
            'kurun_waktu' => 4,
        ];
        $ai = [0 => [
            'jenis_naskah' => 'Surat Keputusan',
            'kategori_arsip' => 'Dinamis Aktif',
            'nama_berkas' => 'Surat Keputusan SK Gubernur',
            'ai_status' => 'AUTO',
            'ai_confidence' => 0.86,
            'ai_metadata' => [],
        ]];

        $row = (new ArchiveTransformService())->transform($sheet, $mapping, [], $ai, 'kegiatan')[0];

        $this->assertSame('HGB', $row['indeks']);
        $this->assertSame('593', $row['kode_klasifikasi']);
        $this->assertSame('Belum', $row['status_authentication']);
        $this->assertSame('AUTO', $row['ai_status']);
    }
}
