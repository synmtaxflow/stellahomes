<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Payment;
use App\Models\RentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Show rent status report - students with their rent expiration status
     */
    public function rentStatus()
    {
        $students = Student::with(['bed', 'room.block'])
            ->where('status', 'active')
            ->whereNull('check_out_date')
            ->get();

        $today = Carbon::today();
        $studentsWithStatus = [];

        foreach ($students as $student) {
            // Get rent details
            $rentPrice = 0;
            $rentDuration = null;
            $semesterMonths = null;
            $paymentFrequency = null;

            if ($student->bed_id) {
                $bed = $student->bed;
                $rentPrice = $bed->rent_price ?? 0;
                $rentDuration = $bed->rent_duration;
                $semesterMonths = $bed->semester_months;
                $paymentFrequency = $bed->payment_frequency;
            } elseif ($student->room_id) {
                $room = $student->room;
                $rentPrice = $room->rent_price ?? 0;
                $rentDuration = $room->rent_duration;
                $semesterMonths = $room->semester_months;
                $paymentFrequency = $room->payment_frequency;
            }

            // Get last payment with period_start_date and period_end_date (only from payments table)
            $lastPayment = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->whereNotNull('period_end_date')
                ->whereNotNull('period_start_date')
                ->orderBy('period_end_date', 'desc')
                ->orderBy('payment_date', 'desc')
                ->first();

            $rentStartDate = null;
            $rentEndDate = null;
            $status = 'unknown'; // unknown, expired, warning, active
            $daysRemaining = null;

            if ($lastPayment && $lastPayment->period_start_date && $lastPayment->period_end_date) {
                // Use dates from payments table
                $rentStartDate = Carbon::parse($lastPayment->period_start_date);
                $rentEndDate = Carbon::parse($lastPayment->period_end_date);
                $daysRemaining = $today->diffInDays($rentEndDate, false);

                if ($daysRemaining < 0) {
                    $status = 'expired'; // Red - rent has expired
                } elseif ($daysRemaining <= 15) {
                    $status = 'warning'; // Yellow - less than 15 days remaining
                } else {
                    $status = 'active'; // Green - rent is active
                }
            } else {
                // No payment recorded - status is unknown
                $status = 'unknown';
            }

            $studentsWithStatus[] = [
                'student' => $student,
                'rent_start_date' => $rentStartDate,
                'rent_end_date' => $rentEndDate,
                'status' => $status,
                'days_remaining' => $daysRemaining,
                'rent_price' => $rentPrice,
                'rent_duration' => $rentDuration,
            ];
        }

        return view('reports.rent-status', compact('studentsWithStatus'));
    }

    /**
     * Export rent status report to PDF
     */
    public function rentStatusExportPdf()
    {
        $students = Student::with(['bed', 'room.block'])
            ->where('status', 'active')
            ->whereNull('check_out_date')
            ->get();

        $today = Carbon::today();
        $studentsWithStatus = [];

        foreach ($students as $student) {
            // Get rent details
            $rentPrice = 0;
            $rentDuration = null;

            if ($student->bed_id) {
                $bed = $student->bed;
                $rentPrice = $bed->rent_price ?? 0;
                $rentDuration = $bed->rent_duration;
            } elseif ($student->room_id) {
                $room = $student->room;
                $rentPrice = $room->rent_price ?? 0;
                $rentDuration = $room->rent_duration;
            }

            // Get last payment with period dates (only from payments table)
            $lastPayment = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->whereNotNull('period_end_date')
                ->whereNotNull('period_start_date')
                ->orderBy('period_end_date', 'desc')
                ->orderBy('payment_date', 'desc')
                ->first();

            $rentStartDate = null;
            $rentEndDate = null;
            $status = 'unknown';
            $daysRemaining = null;

            if ($lastPayment && $lastPayment->period_start_date && $lastPayment->period_end_date) {
                $rentStartDate = Carbon::parse($lastPayment->period_start_date);
                $rentEndDate = Carbon::parse($lastPayment->period_end_date);
                $daysRemaining = $today->diffInDays($rentEndDate, false);

                if ($daysRemaining < 0) {
                    $status = 'expired';
                } elseif ($daysRemaining <= 15) {
                    $status = 'warning';
                } else {
                    $status = 'active';
                }
            }

            $studentsWithStatus[] = [
                'student' => $student,
                'rent_start_date' => $rentStartDate,
                'rent_end_date' => $rentEndDate,
                'status' => $status,
                'days_remaining' => $daysRemaining,
                'rent_price' => $rentPrice,
                'rent_duration' => $rentDuration,
            ];
        }

        // Filter out students with unknown status (no payments)
        $studentsWithStatus = array_filter($studentsWithStatus, function($item) {
            return $item['status'] !== 'unknown';
        });

        // Generate HTML for PDF
        $html = view('reports.rent-status-pdf', compact('studentsWithStatus', 'today'))->render();
        
        // Return as downloadable HTML (can be printed as PDF from browser)
        return response()->streamDownload(function() use ($html) {
            echo $html;
        }, 'rent-status-report-' . date('Y-m-d') . '.html', [
            'Content-Type' => 'text/html',
        ]);
    }

    /**
     * Export rent status report to Excel
     */
    public function rentStatusExportExcel()
    {
        $students = Student::with(['bed', 'room.block'])
            ->where('status', 'active')
            ->whereNull('check_out_date')
            ->get();

        $today = Carbon::today();
        $studentsWithStatus = [];

        foreach ($students as $student) {
            // Get rent details
            $rentPrice = 0;
            $rentDuration = null;

            if ($student->bed_id) {
                $bed = $student->bed;
                $rentPrice = $bed->rent_price ?? 0;
                $rentDuration = $bed->rent_duration;
            } elseif ($student->room_id) {
                $room = $student->room;
                $rentPrice = $room->rent_price ?? 0;
                $rentDuration = $room->rent_duration;
            }

            // Get last payment with period dates (only from payments table)
            $lastPayment = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->whereNotNull('period_end_date')
                ->whereNotNull('period_start_date')
                ->orderBy('period_end_date', 'desc')
                ->orderBy('payment_date', 'desc')
                ->first();

            $rentStartDate = null;
            $rentEndDate = null;
            $status = 'unknown';
            $daysRemaining = null;

            if ($lastPayment && $lastPayment->period_start_date && $lastPayment->period_end_date) {
                $rentStartDate = Carbon::parse($lastPayment->period_start_date);
                $rentEndDate = Carbon::parse($lastPayment->period_end_date);
                $daysRemaining = $today->diffInDays($rentEndDate, false);

                if ($daysRemaining < 0) {
                    $status = 'expired';
                } elseif ($daysRemaining <= 15) {
                    $status = 'warning';
                } else {
                    $status = 'active';
                }
            }

            $studentsWithStatus[] = [
                'student' => $student,
                'rent_start_date' => $rentStartDate,
                'rent_end_date' => $rentEndDate,
                'status' => $status,
                'days_remaining' => $daysRemaining,
                'rent_price' => $rentPrice,
                'rent_duration' => $rentDuration,
            ];
        }

        // Filter out students with unknown status (no payments)
        $studentsWithStatus = array_filter($studentsWithStatus, function($item) {
            return $item['status'] !== 'unknown';
        });

        $data = [];
        $data[] = ['Student Name', 'Block', 'Room', 'Bed', 'Rent Start Date', 'Rent End Date', 'Days Remaining', 'Status', 'Rent Price'];

        foreach ($studentsWithStatus as $item) {
            $data[] = [
                $item['student']->full_name,
                $item['student']->room ? $item['student']->room->block->name : 'N/A',
                $item['student']->room ? $item['student']->room->name : 'N/A',
                $item['student']->bed ? $item['student']->bed->name : 'N/A',
                $item['rent_start_date'] ? $item['rent_start_date']->format('d/m/Y') : 'N/A',
                $item['rent_end_date'] ? $item['rent_end_date']->format('d/m/Y') : 'N/A',
                $item['days_remaining'] !== null ? ($item['days_remaining'] < 0 ? abs($item['days_remaining']) . ' days overdue' : $item['days_remaining'] . ' days') : 'N/A',
                ucfirst($item['status']),
                'Tsh ' . number_format($item['rent_price'], 2),
            ];
        }

        $filename = 'rent-status-report-' . date('Y-m-d') . '.csv';
        
        // Export as CSV (Excel can open CSV files)
        return response()->streamDownload(function() use ($data) {
            $file = fopen('php://output', 'w');
            // Add BOM for UTF-8 (Excel compatibility)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Show payment report with filters
     */
    public function paymentReport(Request $request)
    {
        $filterType = $request->get('filter_type', 'month'); // month, year
        $filterValue = $request->get('filter_value', date('Y-m')); // YYYY-MM for month, YYYY for year

        $query = Payment::with(['student.room.block', 'student.bed'])
            ->where('status', 'completed');

        if ($filterType === 'month' && $filterValue) {
            // Filter by month (YYYY-MM format)
            $year = substr($filterValue, 0, 4);
            $month = substr($filterValue, 5, 2);
            $query->whereYear('payment_date', $year)
                  ->whereMonth('payment_date', $month);
        } elseif ($filterType === 'year' && $filterValue) {
            // Filter by year
            $query->whereYear('payment_date', $filterValue);
        }

        $payments = $query->orderBy('payment_date', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->get();

        // Calculate totals
        $totalExpected = 0;
        $totalPaid = $payments->sum('amount');
        $totalPending = 0;

        // Group by student to calculate expected amounts
        $studentsData = [];
        foreach ($payments as $payment) {
            $studentId = $payment->student_id;
            
            if (!isset($studentsData[$studentId])) {
                $student = $payment->student;
                $rentPrice = 0;
                $rentDuration = null;
                $paymentFrequency = null;

                if ($student->bed_id) {
                    $bed = $student->bed;
                    $rentPrice = $bed->rent_price ?? 0;
                    $rentDuration = $bed->rent_duration;
                    $paymentFrequency = $bed->payment_frequency;
                } elseif ($student->room_id) {
                    $room = $student->room;
                    $rentPrice = $room->rent_price ?? 0;
                    $rentDuration = $room->rent_duration;
                    $paymentFrequency = $room->payment_frequency;
                }

                // Calculate expected amount based on filter period
                $expectedAmount = 0;
                if ($filterType === 'month') {
                    // For monthly filter, calculate expected for that month
                    if ($rentDuration === 'monthly' && $paymentFrequency) {
                        $frequencyMonths = $this->getFrequencyMonths($paymentFrequency);
                        // Calculate how many payment periods in the selected month
                        // For simplicity, use one period's expected amount
                        $expectedAmount = $rentPrice * ($frequencyMonths ?? 1);
                    } elseif ($rentDuration === 'semester') {
                        // For semester, calculate proportion for the month
                        $expectedAmount = $rentPrice / 4; // Approximate monthly portion
                    } else {
                        $expectedAmount = $rentPrice;
                    }
                } else {
                    // For yearly filter, calculate expected for the year
                    if ($rentDuration === 'monthly' && $paymentFrequency) {
                        $frequencyMonths = $this->getFrequencyMonths($paymentFrequency);
                        // Calculate how many payment periods in a year
                        $periodsPerYear = 12 / ($frequencyMonths ?? 1);
                        $expectedAmount = $rentPrice * $periodsPerYear;
                    } elseif ($rentDuration === 'semester') {
                        // For semester, calculate for the year (usually 2 semesters)
                        $expectedAmount = $rentPrice * 2;
                    } else {
                        $expectedAmount = $rentPrice * 12; // Monthly for year
                    }
                }

                $studentsData[$studentId] = [
                    'student' => $student,
                    'expected_amount' => $expectedAmount,
                    'paid_amount' => 0,
                ];
            }

            $studentsData[$studentId]['paid_amount'] += $payment->amount;
        }

        // Calculate totals
        foreach ($studentsData as $data) {
            $totalExpected += $data['expected_amount'];
            $pending = $data['expected_amount'] - $data['paid_amount'];
            if ($pending > 0) {
                $totalPending += $pending;
            }
        }

        return view('reports.payment-report', compact('payments', 'studentsData', 'totalExpected', 'totalPaid', 'totalPending', 'filterType', 'filterValue'));
    }

    private function getFrequencyMonths($paymentFrequency)
    {
        $frequencyMap = [
            'one_month' => 1,
            'two_months' => 2,
            'three_months' => 3,
            'four_months' => 4,
            'five_months' => 5,
            'six_months' => 6,
        ];
        
        return $frequencyMap[$paymentFrequency] ?? null;
    }
}

