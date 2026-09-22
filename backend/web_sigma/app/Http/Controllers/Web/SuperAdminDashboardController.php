<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FireRisk;
use App\Models\Organization;
use App\Models\Region;
use App\Models\SystemConfiguration;
use App\Models\User;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        $risks = FireRisk::query()
            ->selectRaw('risk_level, count(*) as total')
            ->groupBy('risk_level')
            ->pluck('total', 'risk_level');

        $systemStatus = SystemConfiguration::query()
            ->where('key', 'system_status')
            ->value('value') ?? 'Belum dikonfigurasi';

        return view('super_admin.dashboard', [
            'userCount' => User::count(),
            'activeUserCount' => User::whereNotNull('email_verified_at')->count(),
            'organizationCount' => Organization::count(),
            'activeOrganizationCount' => Organization::where('status', 'active')->count(),
            'regionCount' => Region::count(),
            'regionLevels' => Region::query()->selectRaw('level, count(*) as total')->groupBy('level')->pluck('total', 'level'),
            'systemStatus' => $systemStatus,
            'recentActivities' => AuditLog::with('user')->latest()->take(5)->get(),
            'auditActivityByDay' => AuditLog::query()
                ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date'),
            'riskCounts' => $risks,
            'extremeRisks' => FireRisk::with('region')->where('risk_level', 'extreme')->latest('calculated_at')->take(3)->get(),
        ]);
    }

    public function export()
    {
        $logs = AuditLog::with('user')
            ->latest('created_at')
            ->get();

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Audit Trail');

        /*
    |--------------------------------------------------------------------------
    | Default Font
    |--------------------------------------------------------------------------
    */

        $spreadsheet->getDefaultStyle()
            ->getFont()
            ->setName('Arial')
            ->setSize(10);

        /*
    |--------------------------------------------------------------------------
    | LOGO
    |--------------------------------------------------------------------------
    */

        $logoPath = public_path('images/logo.jpeg');

        if (file_exists($logoPath)) {
            $drawing = new Drawing();

            $drawing->setName('SIGMA Logo');
            $drawing->setDescription('Logo SIGMA');
            $drawing->setPath($logoPath);

            $drawing->setHeight(55);
            $drawing->setCoordinates('A1');

            $drawing->setOffsetX(8);
            $drawing->setOffsetY(5);

            $drawing->setWorksheet($sheet);
        }

        /*
    |--------------------------------------------------------------------------
    | HEADER LAPORAN
    |--------------------------------------------------------------------------
    */

        $sheet->mergeCells('B1:G1');
        $sheet->setCellValue('B1', 'SIGMA');

        $sheet->mergeCells('B2:G2');
        $sheet->setCellValue(
            'B2',
            'LAPORAN AUDIT SISTEM'
        );

        $sheet->mergeCells('B3:G3');
        $sheet->setCellValue(
            'B3',
            'Command Center Karhutla Nasional'
        );

        /*
    |--------------------------------------------------------------------------
    | STYLE JUDUL
    |--------------------------------------------------------------------------
    */

        $sheet->getStyle('B1')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => 18,
                'color' => [
                    'rgb' => 'C82828',
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('B2')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => 13,
                'color' => [
                    'rgb' => '1E293B',
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('B3')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10,
                'color' => [
                    'rgb' => '64748B',
                ],
            ],
        ]);

        /*
    |--------------------------------------------------------------------------
    | INFORMASI LAPORAN
    |--------------------------------------------------------------------------
    */

        $sheet->mergeCells('A5:C5');
        $sheet->setCellValue(
            'A5',
            'Tanggal Export'
        );

        $sheet->mergeCells('D5:G5');
        $sheet->setCellValue(
            'D5',
            now()->format('d F Y H:i:s') . ' WIB'
        );

        $sheet->mergeCells('A6:C6');
        $sheet->setCellValue(
            'A6',
            'Total Aktivitas'
        );

        $sheet->mergeCells('D6:G6');
        $sheet->setCellValue(
            'D6',
            $logs->count() . ' aktivitas'
        );

        $sheet->getStyle('A5:G6')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'F8FAFC',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => [
                        'rgb' => 'E2E8F0',
                    ],
                ],
            ],
        ]);

        $sheet->getStyle('A5:A6')->getFont()->setBold(true);

        /*
    |--------------------------------------------------------------------------
    | TABLE HEADER
    |--------------------------------------------------------------------------
    */

        $headerRow = 8;

        $headers = [
            'No',
            'Waktu',
            'Nama Pengguna',
            'Role',
            'Aksi',
            'Modul',
            'Deskripsi',
            'IP Address',
        ];

        $sheet->fromArray(
            $headers,
            null,
            'A' . $headerRow
        );

        $lastColumn = 'H';

        /*
    |--------------------------------------------------------------------------
    | STYLE TABLE HEADER
    |--------------------------------------------------------------------------
    */

        $sheet->getStyle(
            "A{$headerRow}:{$lastColumn}{$headerRow}"
        )->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => [
                    'rgb' => 'FFFFFF',
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'C82828',
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => [
                        'rgb' => 'CBD5E1',
                    ],
                ],
            ],
        ]);

        /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

        $row = $headerRow + 1;

        foreach ($logs as $index => $log) {

            $roles = $log->user?->roles
                ? $log->user->roles->pluck('name')->join(', ')
                : '-';

            $sheet->fromArray([
                $index + 1,

                $log->created_at
                    ? $log->created_at->format('d-m-Y H:i:s')
                    : '-',

                $log->user?->name ?? 'Sistem',

                $roles ?: '-',

                $log->action ?? '-',

                $log->module ?? '-',

                $log->description ?? '-',

                $log->ip_address ?? '-',

            ], null, 'A' . $row);

            $row++;
        }

        /*
    |--------------------------------------------------------------------------
    | STYLE DATA TABLE
    |--------------------------------------------------------------------------
    */

        $lastDataRow = max($row - 1, $headerRow);

        $sheet->getStyle(
            "A{$headerRow}:{$lastColumn}{$lastDataRow}"
        )->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => [
                        'rgb' => 'E2E8F0',
                    ],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);

        /*
    |--------------------------------------------------------------------------
    | ALIGNMENT
    |--------------------------------------------------------------------------
    */

        $sheet->getStyle(
            "A" . ($headerRow + 1) . ":A{$lastDataRow}"
        )->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle(
            "B" . ($headerRow + 1) . ":B{$lastDataRow}"
        )->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle(
            "H" . ($headerRow + 1) . ":H{$lastDataRow}"
        )->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        /*
    |--------------------------------------------------------------------------
    | COLUMN WIDTH
    |--------------------------------------------------------------------------
    */

        $sheet->getColumnDimension('A')->setWidth(7);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(24);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(45);
        $sheet->getColumnDimension('H')->setWidth(18);

        /*
    |--------------------------------------------------------------------------
    | ROW HEIGHT
    |--------------------------------------------------------------------------
    */

        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(24);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension($headerRow)->setRowHeight(30);

        /*
    |--------------------------------------------------------------------------
    | FREEZE HEADER
    |--------------------------------------------------------------------------
    */

        $sheet->freezePane('A9');

        /*
    |--------------------------------------------------------------------------
    | AUTO FILTER
    |--------------------------------------------------------------------------
    */

        $sheet->setAutoFilter(
            "A{$headerRow}:{$lastColumn}{$lastDataRow}"
        );

        /*
    |--------------------------------------------------------------------------
    | PAGE / VIEW
    |--------------------------------------------------------------------------
    */

        $sheet->getSheetView()->setZoomScale(90);

        /*
    |--------------------------------------------------------------------------
    | OUTPUT
    |--------------------------------------------------------------------------
    */

        $fileName = 'sigma-audit-report-' . now()->format('Y-m-d-His') . '.xlsx';

        $tempFile = tempnam(
            sys_get_temp_dir(),
            'sigma_audit_'
        );

        $writer = new Xlsx($spreadsheet);

        $writer->save($tempFile);

        return response()
            ->download(
                $tempFile,
                $fileName,
                [
                    'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            )
            ->deleteFileAfterSend(true);
    }
}
