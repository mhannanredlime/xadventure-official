<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Package;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\PromoRedemption;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        try {
            Log::info('Reports page accessed');
            
            $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());
            
            // Convert string dates to Carbon instances if needed
            if (is_string($dateFrom)) {
                $dateFrom = Carbon::parse($dateFrom);
            }
            if (is_string($dateTo)) {
                $dateTo = Carbon::parse($dateTo);
            }

            Log::info('Date range: ' . $dateFrom->format('Y-m-d') . ' to ' . $dateTo->format('Y-m-d'));

            // Comprehensive data collection
            $reservationSummary = $this->getReservationSummary($dateFrom, $dateTo);
            $financialSummary = $this->getFinancialSummary($dateFrom, $dateTo);
            $packagePerformance = $this->getPackagePerformance($dateFrom, $dateTo);
            $customerAnalytics = $this->getCustomerAnalytics($dateFrom, $dateTo);
            $promoCodePerformance = $this->getPromoCodePerformance($dateFrom, $dateTo);
            $monthlyTrends = $this->getMonthlyTrends();
            $topCustomers = $this->getTopCustomers($dateFrom, $dateTo);
            $recentActivity = $this->getRecentActivity();
            
            // Calculate percentages for reservation status
            $total = $reservationSummary['total'];
            $reservationSummary['confirmed_percentage'] = $total > 0 ? ($reservationSummary['confirmed'] / $total) * 100 : 0;
            $reservationSummary['pending_percentage'] = $total > 0 ? ($reservationSummary['pending'] / $total) * 100 : 0;
            $reservationSummary['completed_percentage'] = $total > 0 ? ($reservationSummary['completed'] / $total) * 100 : 0;
            $reservationSummary['cancelled_percentage'] = $total > 0 ? ($reservationSummary['cancelled'] / $total) * 100 : 0;
            
            // Calculate percentages for financial status
            $financialSummary['paid_percentage'] = $financialSummary['total_revenue'] > 0 ? ($financialSummary['paid_reservations'] / $financialSummary['total_revenue']) * 100 : 0;
            $financialSummary['pending_percentage'] = $financialSummary['total_revenue'] > 0 ? ($financialSummary['pending_payments'] / $financialSummary['total_revenue']) * 100 : 0;
            $financialSummary['partial_percentage'] = $financialSummary['total_revenue'] > 0 ? ($financialSummary['partial_payments'] / $financialSummary['total_revenue']) * 100 : 0;

            Log::info('All data collected, rendering view...');

            return view('admin.reports.index', compact(
                'reservationSummary',
                'financialSummary', 
                'packagePerformance',
                'customerAnalytics',
                'promoCodePerformance',
                'monthlyTrends',
                'topCustomers',
                'recentActivity',
                'dateFrom',
                'dateTo'
            ));
        } catch (\Exception $e) {
            Log::error('Reports page error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'An error occurred while loading the reports. Please try again.');
        }
    }

    private function getReservationSummary($dateFrom, $dateTo)
    {
        try {
            $reservations = Reservation::whereBetween('date', [$dateFrom, $dateTo]);
            
            return [
                'total' => $reservations->count(),
                'confirmed' => $reservations->where('booking_status', 'confirmed')->count(),
                'pending' => $reservations->where('booking_status', 'pending')->count(),
                'cancelled' => $reservations->where('booking_status', 'cancelled')->count(),
                'completed' => $reservations->where('booking_status', 'completed')->count(),
                'avg_party_size' => $reservations->avg('party_size') ?? 0,
                'total_guests' => $reservations->sum('party_size'),
            ];
        } catch (\Exception $e) {
            Log::error('Reservation summary error: ' . $e->getMessage());
            return [
                'total' => 0,
                'confirmed' => 0,
                'pending' => 0,
                'cancelled' => 0,
                'completed' => 0,
                'avg_party_size' => 0,
                'total_guests' => 0,
            ];
        }
    }

    private function getFinancialSummary($dateFrom, $dateTo)
    {
        try {
            $reservations = Reservation::whereBetween('date', [$dateFrom, $dateTo]);
            
            return [
                'total_revenue' => $reservations->sum('total_amount'),
                'total_deposits' => $reservations->sum('deposit_amount'),
                'total_balance' => $reservations->sum('balance_amount'),
                'paid_reservations' => $reservations->where('payment_status', 'paid')->sum('total_amount'),
                'pending_payments' => $reservations->where('payment_status', 'pending')->sum('total_amount'),
                'partial_payments' => $reservations->where('payment_status', 'partial')->sum('total_amount'),
                'avg_reservation_value' => $reservations->avg('total_amount') ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('Financial summary error: ' . $e->getMessage());
            return [
                'total_revenue' => 0,
                'total_deposits' => 0,
                'total_balance' => 0,
                'paid_reservations' => 0,
                'pending_payments' => 0,
                'partial_payments' => 0,
                'avg_reservation_value' => 0,
            ];
        }
    }

    private function getPackagePerformance($dateFrom, $dateTo)
    {
        try {
            Log::info('Starting package performance query...');
            
            // Use manual joins for better performance and reliability
            $packages = Package::select('packages.*')
                ->selectRaw('COUNT(DISTINCT reservations.id) as reservations_count')
                ->selectRaw('SUM(reservations.total_amount) as reservations_sum_total_amount')
                ->leftJoin('package_variants', 'packages.id', '=', 'package_variants.package_id')
                ->leftJoin('reservations', function($join) use ($dateFrom, $dateTo) {
                    $join->on('package_variants.id', '=', 'reservations.package_variant_id')
                         ->whereBetween('reservations.date', [$dateFrom, $dateTo]);
                })
                ->groupBy('packages.id', 'packages.name', 'packages.subtitle', 'packages.type', 'packages.min_participants', 'packages.max_participants', 'packages.image_path', 'packages.notes', 'packages.details', 'packages.selected_weekday', 'packages.selected_weekend', 'packages.is_active', 'packages.created_at', 'packages.updated_at')
                ->orderBy('reservations_count', 'desc')
                ->take(10)
                ->get();
            
            Log::info('Package performance query completed. Found ' . $packages->count() . ' packages');
            
            return $packages;
        } catch (\Exception $e) {
            Log::error('Package performance error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getCustomerAnalytics($dateFrom, $dateTo)
    {
        try {
            $totalCustomers = Customer::count();
            $newCustomers = Customer::whereBetween('created_at', [$dateFrom, $dateTo])->count();
            
            // Calculate repeat customers (customers with more than 1 reservation in the period)
            $repeatCustomers = Customer::whereHas('reservations', function($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('date', [$dateFrom, $dateTo]);
            }, '>', 1)->count();

            return [
                'total_customers' => $totalCustomers,
                'new_customers' => $newCustomers,
                'repeat_customers' => $repeatCustomers,
                'customer_growth_rate' => $totalCustomers > 0 ? ($newCustomers / $totalCustomers) * 100 : 0,
            ];
        } catch (\Exception $e) {
            Log::error('Customer analytics error: ' . $e->getMessage());
            return [
                'total_customers' => 0,
                'new_customers' => 0,
                'repeat_customers' => 0,
                'customer_growth_rate' => 0,
            ];
        }
    }

    private function getPromoCodePerformance($dateFrom, $dateTo)
    {
        try {
            return PromoCode::withCount(['redemptions' => function($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            }])
            ->withSum(['redemptions' => function($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            }], 'discount_amount')
            ->orderBy('redemptions_count', 'desc')
            ->take(10)
            ->get();
        } catch (\Exception $e) {
            Log::error('Promo code performance error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getMonthlyTrends()
    {
        try {
            return Reservation::selectRaw('
                YEAR(date) as year,
                MONTH(date) as month,
                COUNT(*) as total_reservations,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as avg_revenue
            ')
            ->where('date', '>=', Carbon::now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
        } catch (\Exception $e) {
            Log::error('Monthly trends error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getTopCustomers($dateFrom, $dateTo)
    {
        try {
            Log::info('Starting top customers query...');
            
            $customers = Customer::select('customers.*')
                ->selectRaw('COUNT(DISTINCT reservations.id) as reservations_count')
                ->selectRaw('SUM(reservations.total_amount) as reservations_sum_total_amount')
                ->leftJoin('reservations', function($join) use ($dateFrom, $dateTo) {
                    $join->on('customers.id', '=', 'reservations.customer_id')
                         ->whereBetween('reservations.date', [$dateFrom, $dateTo]);
                })
                ->groupBy('customers.id', 'customers.name', 'customers.email', 'customers.phone', 'customers.address', 'customers.created_at', 'customers.updated_at')
                ->orderBy('reservations_sum_total_amount', 'desc')
                ->take(10)
                ->get();
            
            Log::info('Top customers query completed. Found ' . $customers->count() . ' customers');
            
            return $customers;
        } catch (\Exception $e) {
            Log::error('Top customers error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getRecentActivity()
    {
        try {
            return Reservation::with(['customer', 'packageVariant.package'])
                ->latest()
                ->take(10)
                ->get();
        } catch (\Exception $e) {
            Log::error('Recent activity error: ' . $e->getMessage());
            return collect();
        }
    }

    public function export(Request $request)
    {
        try {
            $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());
            
            // Convert string dates to Carbon instances if needed
            if (is_string($dateFrom)) {
                $dateFrom = Carbon::parse($dateFrom);
            }
            if (is_string($dateTo)) {
                $dateTo = Carbon::parse($dateTo);
            }

            $reservations = Reservation::with(['customer', 'packageVariant.package', 'scheduleSlot'])
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->get();

            $filename = 'reports_' . $dateFrom->format('Y-m-d') . '_to_' . $dateTo->format('Y-m-d') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($reservations) {
                $file = fopen('php://output', 'w');
                
                // Add headers
                fputcsv($file, [
                    'Booking Code',
                    'Date',
                    'Customer Name',
                    'Customer Phone',
                    'Package Name',
                    'Schedule Slot',
                    'Party Size',
                    'Total Amount',
                    'Deposit Amount',
                    'Balance Amount',
                    'Booking Status',
                    'Payment Status',
                    'Created At'
                ]);

                // Add data
                foreach ($reservations as $reservation) {
                    fputcsv($file, [
                        $reservation->booking_code,
                        $reservation->date,
                        $reservation->customer->name ?? 'N/A',
                        $reservation->customer->phone ?? 'N/A',
                        $reservation->packageVariant->package->name ?? 'N/A',
                        $reservation->scheduleSlot->name ?? 'N/A',
                        $reservation->party_size,
                        $reservation->total_amount,
                        $reservation->deposit_amount ?? 0,
                        $reservation->balance_amount ?? 0,
                        $reservation->booking_status,
                        $reservation->payment_status,
                        $reservation->created_at
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while exporting the data. Please try again.');
        }
    }
}
