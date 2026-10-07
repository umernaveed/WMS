<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Session;
use Validator;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Order;
use App\Models\BankInfo;
use App\Models\Contact;
use App\Models\Newsletter;
use App\Models\Transaction;
use App\Models\Courier;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Former;
use Image;
use File;
use DB;
use commonHelper;
use URL;
use Mail;

class DashboardControllor extends Controller
{

    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }
public function getProcessingAnalytics(Request $request)
{
    $filter = $request->input('filter', 'today');
    $data = [];
    
    // Get all staff members who have added packages (processors)
    // This includes both admin users and couriers who process packages
    $staffMembers = DB::table('users')
        ->select('user_id', 'first_name', 'last_name', 'email', DB::raw("'staff' as processor_type"))
        ->where('status', 1) // Only active users
        ->whereIn('user_id', function($query) {
            $query->select('addedby_id')
                ->from('packages')
                ->whereNotNull('addedby_id')
                ->where('addedby_id', '>', 0)
                ->distinct();
        })
        ->get();
    
    // Also include couriers who might have processed packages
    $couriers = DB::table('couriers')
        ->select('id as user_id', 'name as first_name', DB::raw("'' as last_name"), 'email', DB::raw("'courier' as processor_type"))
        ->where('status', 1)
        ->whereIn('id', function($query) {
            $query->select('courier_id')
                ->from('packages')
                ->whereNotNull('courier_id')
                ->where('courier_id', '>', 0)
                ->distinct();
        })
        ->get();
    
    // Merge both collections
    $allProcessors = $staffMembers->concat($couriers);
    
    foreach($allProcessors as $processor) {
        $query = DB::table('packages');
        
        // Determine which ID field to use
        $idField = $processor->processor_type === 'courier' ? 'courier_id' : 'addedby_id';
        $processorId = $processor->user_id;
        
        $query->where($idField, $processorId);
        
        // Apply date filter
        switch($filter) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'all':
            default:
                // No date filter for all time
                break;
        }
        
        $count = $query->count();
        
        // Only include if they have processed at least one package in the selected period
        if($count > 0) {
            $processorName = isset($processor->first_name) 
                ? $processor->first_name . ' ' . $processor->last_name 
                : $processor->name;
            
            $data[] = [
                'processor' => trim($processorName),
                'packages' => $count,
                'email' => $processor->email ?? '',
                'id' => $processorId,
                'type' => $processor->processor_type
            ];
        }
    }
    
    // Sort by packages processed (descending)
    usort($data, function($a, $b) {
        return $b['packages'] - $a['packages'];
    });
    
    return response()->json([
        'success' => true,
        'data' => $data,
        'filter' => $filter,
        'total_processors' => count($data),
        'total_packages' => array_sum(array_column($data, 'packages'))
    ]);
}
   public function dashboard(Request $request) {
    $data['title'] = 'Dashboard';
    $data['class'] = 'dashboard';
    $total = 0;
    $data['setting'] = DB::table('settings')->first();
    
    // DYNAMIC: Total Packages at US Warehouse (status = 3 or based on your warehouse status)
    // Adjust the status value based on your package_status table
    $data['wherehouse'] = DB::table('packages')
        ->where(function($query) {
            $query->where('packages.status', 3) // Assuming 3 = "At Warehouse"
                  ->orWhere('packages.status_name', 'LIKE', '%warehouse%')
                  ->orWhere('packages.status_name', 'LIKE', '%US Warehouse%');
        })
        ->count();
    
    // DYNAMIC: Total Packages in Transit (status = 2 or based on your transit status)
    $data['in_transit'] = DB::table('packages')
        ->where(function($query) {
            $query->where('packages.status', 2) // Assuming 2 = "In Transit"
                  ->orWhere('packages.status_name', 'LIKE', '%transit%')
                  ->orWhere('packages.status_name', 'LIKE', '%shipped%');
        })
        ->count();
    $data['china_warehouse'] = DB::table('packages')
        ->where(function($query) {
            $query->where('packages.warehouse', 'LIKE', '%china%')
                  ->orWhere('packages.status_name', 'LIKE', '%china%');
        })
        ->count();
    
    // Keep other existing data if needed
    $data['outstanding_package'] = DB::table('packages')->where('packages.status', 1)->count();
    $data['outstanding_invoice'] = DB::table('invoices')->where('invoices.status', 0)->count();
    $data['total_packages'] = DB::table('packages')->count();
    $data['unknown_packages'] = DB::table('packages')->where(function($query) {
            $query->where('mailbox', '0')
                  ->orWhere('mailbox', '')
                  ->orWhereNull('mailbox');
        })->count();
    $data['assigned_unknowns'] = DB::table('packages')
        ->where('is_request', 1)
        ->where(function($query) {
            $query->where('mailbox', '!=', '0')
                  ->where('mailbox', '!=', '')
                  ->whereNotNull('mailbox');
        })
        ->count();
    $data['manifest_ready'] = DB::table('packages')
        ->where('is_added_manifest', 0)
        ->where('is_temprary_logs', 0)
        ->count();
    $data['pending_requests'] = DB::table('packages')->where('is_request', 1)->count();
    $data['active_clients'] = DB::table('users')->where('status', 1)->count();
    $data['active_couriers'] = DB::table('couriers')->where('status', 1)->count();
    $data['today_packages'] = DB::table('packages')->whereDate('created_at', Carbon::today())->count();
    $data['dropoffs_today'] = DB::table('packages')->whereDate('date_received', Carbon::today()->toDateString())->count();
    $data['week_packages'] = DB::table('packages')
        ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
        ->count();
    $data['month_packages'] = DB::table('packages')
        ->whereMonth('created_at', Carbon::now()->month)
        ->whereYear('created_at', Carbon::now()->year)
        ->count();
    $data['recent_packages'] = DB::table('packages')
        ->select('packeg_id', 'manifest_no', 'supplier_tracking_no', 'courier', 'user_name', 'mailbox', 'status_name', 'created_at')
        ->orderBy('created_at', 'DESC')
        ->limit(6)
        ->get();
    $data['recent_unknowns'] = DB::table('packages')
        ->select('packeg_id', 'manifest_no', 'supplier_tracking_no', 'courier', 'weight', 'freight_type', 'volume_cuft', 'volume_cbm', 'chargeable_weight', 'created_at')
        ->where(function($query) {
            $query->where('mailbox', '0')
                  ->orWhere('mailbox', '')
                  ->orWhereNull('mailbox');
        })
        ->orderBy('created_at', 'DESC')
        ->limit(5)
        ->get();
    $data['status_breakdown'] = DB::table('packages')
        ->select('status_name', DB::raw('COUNT(*) as total'))
        ->groupBy('status_name')
        ->orderBy('total', 'DESC')
        ->limit(5)
        ->get();
    $data['outstanding_bills'] = DB::table('invoices')
        ->join('invoice_packages', 'invoice_packages.invoice_id', '=', 'invoices.invoice_id')
        ->where('invoices.status', 0)
        ->where('invoice_packages.status', 0)
        ->sum('invoice_packages.total');
    
    return view('admin/dashboard/dashboard', $data);
}

public function globalSearch(Request $request)
{
    $term = trim($request->input('q', ''));
    $results = [];

    if (strlen($term) < 2) {
        return response()->json(['results' => $results]);
    }

    $packages = DB::table('packages')
        ->select('packeg_id', 'manifest_no', 'supplier_tracking_no', 'tracking_no', 'mailbox', 'user_name', 'status_name')
        ->where(function($query) use ($term) {
            $query->where('supplier_tracking_no', 'LIKE', "%{$term}%")
                  ->orWhere('tracking_no', 'LIKE', "%{$term}%")
                  ->orWhere('manifest_no', 'LIKE', "%{$term}%")
                  ->orWhere('mailbox', 'LIKE', "%{$term}%")
                  ->orWhere('user_name', 'LIKE', "%{$term}%");
        })
        ->orderBy('created_at', 'DESC')
        ->limit(6)
        ->get();

    foreach ($packages as $package) {
        $results[] = [
            'type' => 'Package',
            'title' => $package->supplier_tracking_no ?: $package->tracking_no ?: $package->manifest_no,
            'meta' => trim(($package->user_name ?: 'Unknown customer') . ' / ' . ($package->status_name ?: 'No status')),
            'url' => url('admin/package/edit/' . $package->tracking_no),
        ];
    }

    $users = DB::table('users')
        ->select('user_id', 'mailbox', 'first_name', 'last_name', 'email')
        ->where(function($query) use ($term) {
            $query->where('mailbox', 'LIKE', "%{$term}%")
                  ->orWhere('first_name', 'LIKE', "%{$term}%")
                  ->orWhere('last_name', 'LIKE', "%{$term}%")
                  ->orWhere('email', 'LIKE', "%{$term}%");
        })
        ->limit(4)
        ->get();

    foreach ($users as $user) {
        $results[] = [
            'type' => 'Client',
            'title' => trim($user->first_name . ' ' . $user->last_name) ?: $user->mailbox,
            'meta' => trim($user->mailbox . ' / ' . $user->email),
            'url' => url('admin/user/profile/' . $user->user_id),
        ];
    }

    $invoices = DB::table('invoices')
        ->select('invoice_id', 'invoice_no', 'mailbox_no', 'user_name', 'status')
        ->where(function($query) use ($term) {
            $query->where('invoice_no', 'LIKE', "%{$term}%")
                  ->orWhere('mailbox_no', 'LIKE', "%{$term}%")
                  ->orWhere('user_name', 'LIKE', "%{$term}%");
        })
        ->limit(4)
        ->get();

    foreach ($invoices as $invoice) {
        $results[] = [
            'type' => 'Invoice',
            'title' => 'Invoice #' . $invoice->invoice_no,
            'meta' => trim(($invoice->user_name ?: $invoice->mailbox_no) . ' / ' . ($invoice->status ? 'Paid' : 'Unpaid')),
            'url' => url('admin/invoice/' . $invoice->invoice_id),
        ];
    }

    return response()->json(['results' => array_slice($results, 0, 10)]);
}

private function getPackageStatistics() {
    $stats = [];
    $currentUserId = session('user_id'); // Get current logged in staff ID directly from session
    
    // Today's packages
    $stats['today'] = DB::table('packages')
        ->whereDate('created_at', Carbon::today())
        ->count();
    
    // Yesterday's packages
    $stats['yesterday'] = DB::table('packages')
        ->whereDate('created_at', Carbon::yesterday())
        ->count();
    
    // This week packages
    $stats['this_week'] = DB::table('packages')
        ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
        ->count();
    
    // Last week packages
    $stats['last_week'] = DB::table('packages')
        ->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
        ->count();
    
    // This month packages
    $stats['this_month'] = DB::table('packages')
        ->whereMonth('created_at', Carbon::now()->month)
        ->whereYear('created_at', Carbon::now()->year)
        ->count();
    
    // Last month packages
    $stats['last_month'] = DB::table('packages')
        ->whereMonth('created_at', Carbon::now()->subMonth()->month)
        ->whereYear('created_at', Carbon::now()->subMonth()->year)
        ->count();
    
    // Daily packages for the last 7 days with dates
    $stats['daily_packages'] = DB::table('packages')
        ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
        ->whereBetween('created_at', [Carbon::now()->subDays(6), Carbon::now()])
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('date', 'desc')
        ->get();
    
    // Hourly distribution for today
    $stats['hourly_today'] = DB::table('packages')
        ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
        ->whereDate('created_at', Carbon::today())
        ->groupBy(DB::raw('HOUR(created_at)'))
        ->orderBy('hour')
        ->get();
    
    // Peak hours (hours with most packages overall)
    $stats['peak_hours'] = DB::table('packages')
        ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as total_count'))
        ->where('created_at', '>=', Carbon::now()->subDays(30))
        ->groupBy(DB::raw('HOUR(created_at)'))
        ->orderBy('total_count', 'desc')
        ->limit(5)
        ->get();
    
    // Weekly distribution for last 4 weeks
    $stats['weekly_trend'] = [];
    for ($i = 3; $i >= 0; $i--) {
        $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
        $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
        
        $stats['weekly_trend'][] = [
            'week' => 'Week ' . (4 - $i) . ' (' . $weekStart->format('M d') . ' - ' . $weekEnd->format('M d') . ')',
            'count' => DB::table('packages')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count(),
            'start_date' => $weekStart->format('Y-m-d'),
            'end_date' => $weekEnd->format('Y-m-d')
        ];
    }
    
    // STAFF PERFORMANCE METRICS
    
    // 1. Staff member who added most packages (all time)
    $stats['top_staff_all_time'] = DB::table('packages')
        ->select('packages.addedby_id', DB::raw('COUNT(*) as package_count'))
        ->where('packages.addedby_id', '>', 0)
        ->groupBy('packages.addedby_id')
        ->orderBy('package_count', 'desc')
        ->limit(1)
        ->first();
    
    // Get staff name for top performer
    if ($stats['top_staff_all_time']) {
        $topStaff = DB::table('users')
            ->where('user_id', $stats['top_staff_all_time']->addedby_id)
            ->first();
        $stats['top_staff_all_time']->name = $topStaff ? ($topStaff->first_name . ' ' . $topStaff->last_name) : 'Unknown';
    }
    
    // 2. Current logged-in staff statistics
    $stats['my_stats'] = [
        'today' => DB::table('packages')
            ->where('addedby_id', $currentUserId)
            ->whereDate('created_at', Carbon::today())
            ->count(),
        'this_week' => DB::table('packages')
            ->where('addedby_id', $currentUserId)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count(),
        'this_month' => DB::table('packages')
            ->where('addedby_id', $currentUserId)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count(),
        'total' => DB::table('packages')
            ->where('addedby_id', $currentUserId)
            ->count(),
        'rank' => null
    ];
    
    // 3. Current staff's rank based on total packages
    $staffRankings = DB::table('packages')
        ->select('addedby_id', DB::raw('COUNT(*) as total'))
        ->where('addedby_id', '>', 0)
        ->groupBy('addedby_id')
        ->orderBy('total', 'desc')
        ->get();
    
    $rank = 1;
    foreach ($staffRankings as $staff) {
        if ($staff->addedby_id == $currentUserId) {
            $stats['my_stats']['rank'] = $rank;
            $stats['my_stats']['total_staff'] = $staffRankings->count();
            break;
        }
        $rank++;
    }
    
    // 4. Get all active staff members with their stats
    $allStaffIds = DB::table('packages')
        ->select('addedby_id')
        ->where('addedby_id', '>', 0)
        ->distinct()
        ->pluck('addedby_id')
        ->toArray();
    
    $stats['all_staff'] = [];
    foreach ($allStaffIds as $staffId) {
        $user = DB::table('users')->where('user_id', $staffId)->first();
        if ($user) {
            $staffData = [
                'id' => $staffId,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'total_packages' => DB::table('packages')->where('addedby_id', $staffId)->count(),
                'today' => DB::table('packages')->where('addedby_id', $staffId)->whereDate('created_at', Carbon::today())->count(),
                'this_week' => DB::table('packages')->where('addedby_id', $staffId)->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
                'this_month' => DB::table('packages')->where('addedby_id', $staffId)->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->count(),
            ];
            
            // Get hourly breakdown for today
            $staffData['hourly_today'] = DB::table('packages')
                ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
                ->where('addedby_id', $staffId)
                ->whereDate('created_at', Carbon::today())
                ->groupBy(DB::raw('HOUR(created_at)'))
                ->orderBy('hour')
                ->get()
                ->keyBy('hour')
                ->toArray();
            
            // Get hourly breakdown for this week
            $staffData['hourly_week'] = DB::table('packages')
                ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
                ->where('addedby_id', $staffId)
                ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->groupBy(DB::raw('HOUR(created_at)'))
                ->orderBy('hour')
                ->get()
                ->keyBy('hour')
                ->toArray();
            
            // Get hourly breakdown for this month
            $staffData['hourly_month'] = DB::table('packages')
                ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
                ->where('addedby_id', $staffId)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->groupBy(DB::raw('HOUR(created_at)'))
                ->orderBy('hour')
                ->get()
                ->keyBy('hour')
                ->toArray();
            
            // Get peak hour (last 30 days)
            $peakHour = DB::table('packages')
                ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
                ->where('addedby_id', $staffId)
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->groupBy(DB::raw('HOUR(created_at)'))
                ->orderBy('count', 'desc')
                ->limit(1)
                ->first();
            
            if ($peakHour) {
                $hour = $peakHour->hour;
                $ampm = $hour >= 12 ? 'PM' : 'AM';
                $hour12 = $hour % 12 ?: 12;
                $staffData['peak_hour'] = $hour12 . ' ' . $ampm;
                $staffData['peak_hour_count'] = $peakHour->count;
            } else {
                $staffData['peak_hour'] = 'N/A';
                $staffData['peak_hour_count'] = 0;
            }
            
            $stats['all_staff'][] = $staffData;
        }
    }
    
    // Sort all staff by total packages
    usort($stats['all_staff'], function($a, $b) {
        return $b['total_packages'] - $a['total_packages'];
    });
    
    // Get top 5 staff
    $stats['top_staff'] = array_slice($stats['all_staff'], 0, 5);
    
    // 5. Staff comparison with average
    $totalStaff = count($stats['all_staff']);
    if ($totalStaff > 0) {
        $totalPackagesByStaff = array_sum(array_column($stats['all_staff'], 'total_packages'));
        $stats['staff_average'] = round($totalPackagesByStaff / $totalStaff, 1);
    } else {
        $stats['staff_average'] = 0;
    }
    
    // 6. Daily breakdown for current staff (last 7 days)
    $stats['my_daily'] = DB::table('packages')
        ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
        ->where('addedby_id', $currentUserId)
        ->whereBetween('created_at', [Carbon::now()->subDays(6), Carbon::now()])
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('date', 'desc')
        ->get();
    
    // 7. Current staff's hourly performance
    $stats['my_hourly'] = DB::table('packages')
        ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
        ->where('addedby_id', $currentUserId)
        ->where('created_at', '>=', Carbon::now()->subDays(30))
        ->groupBy(DB::raw('HOUR(created_at)'))
        ->orderBy('hour')
        ->get();
    
    // 8. Current staff's hourly breakdown for different periods
    $stats['my_hourly_today'] = DB::table('packages')
        ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
        ->where('addedby_id', $currentUserId)
        ->whereDate('created_at', Carbon::today())
        ->groupBy(DB::raw('HOUR(created_at)'))
        ->orderBy('hour')
        ->get()
        ->keyBy('hour')
        ->toArray();
    
    $stats['my_hourly_week'] = DB::table('packages')
        ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
        ->where('addedby_id', $currentUserId)
        ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
        ->groupBy(DB::raw('HOUR(created_at)'))
        ->orderBy('hour')
        ->get()
        ->keyBy('hour')
        ->toArray();
    
    $stats['my_hourly_month'] = DB::table('packages')
        ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
        ->where('addedby_id', $currentUserId)
        ->whereMonth('created_at', Carbon::now()->month)
        ->whereYear('created_at', Carbon::now()->year)
        ->groupBy(DB::raw('HOUR(created_at)'))
        ->orderBy('hour')
        ->get()
        ->keyBy('hour')
        ->toArray();
    
    // 9. Current staff's peak hour
    $myPeakHour = DB::table('packages')
        ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
        ->where('addedby_id', $currentUserId)
        ->where('created_at', '>=', Carbon::now()->subDays(30))
        ->groupBy(DB::raw('HOUR(created_at)'))
        ->orderBy('count', 'desc')
        ->limit(1)
        ->first();
    
    if ($myPeakHour) {
        $hour = $myPeakHour->hour;
        $ampm = $hour >= 12 ? 'PM' : 'AM';
        $hour12 = $hour % 12 ?: 12;
        $stats['my_stats']['peak_hour'] = $hour12 . ' ' . $ampm;
        $stats['my_stats']['peak_hour_count'] = $myPeakHour->count;
    } else {
        $stats['my_stats']['peak_hour'] = 'N/A';
        $stats['my_stats']['peak_hour_count'] = 0;
    }
    
    // 10. Current staff's best day
    $myBestDay = DB::table('packages')
        ->select(DB::raw('DAYOFWEEK(created_at) as day_of_week'), DB::raw('COUNT(*) as count'))
        ->where('addedby_id', $currentUserId)
        ->where('created_at', '>=', Carbon::now()->subDays(90))
        ->groupBy(DB::raw('DAYOFWEEK(created_at)'))
        ->orderBy('count', 'desc')
        ->limit(1)
        ->first();
    
    if ($myBestDay) {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $stats['my_stats']['best_day'] = $days[$myBestDay->day_of_week - 1] ?? 'Unknown';
        $stats['my_stats']['best_day_count'] = $myBestDay->count;
    } else {
        $stats['my_stats']['best_day'] = 'N/A';
        $stats['my_stats']['best_day_count'] = 0;
    }
    
    // 11. Department/Team performance
    $stats['team_performance'] = DB::table('packages')
        ->select('packages.courier_id', 'couriers.name as courier_name', DB::raw('COUNT(*) as total_packages'))
        ->leftJoin('couriers', 'packages.courier_id', '=', 'couriers.id')
        ->where('packages.addedby_id', '>', 0)
        ->whereNotNull('packages.courier_id')
        ->where('packages.courier_id', '>', 0)
        ->groupBy('packages.courier_id', 'couriers.name')
        ->orderBy('total_packages', 'desc')
        ->limit(5)
        ->get();
    
    // Total packages
    $stats['total_all_time'] = DB::table('packages')->count();
    
    // Average packages per day (last 30 days)
    $last30DaysCount = DB::table('packages')
        ->where('created_at', '>=', Carbon::now()->subDays(30))
        ->count();
    $stats['avg_daily_30days'] = round($last30DaysCount / 30, 1);
    
    return $stats;
}

    public function listViewReady(Request $request){
        $is_temprary_logs = 0;
        $columns = array(
                        0 =>'manifest_no',
                        1 =>'supplier_tracking_no',
                        2=> 'courier',
                        3=> 'item_description',
                        4=> 'first_name',
                        5=> 'name',
                        6=> 'created_at'
                    );
        $totalData = DB::table('packages')->where('is_temprary_logs' , $is_temprary_logs)
                            ->where('packages.status' ,  1)
                            ->count();

        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $posts = DB::table('packages')->select('packages.packeg_id','packages.supplier_tracking_no','packages.tracking_no','packages.invoice','packages.manifest_no','packages.courier','packages.weight','packages.item_description','packages.user_name','packages.status_name','packages.created_at')
                        //->join('users', 'packages.mailbox', '=', 'users.mailbox')
                        //->join('package_status', 'package_status.id', '=', 'packages.status')
                        ->where('packages.status' ,  1)
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy('packages.created_at','DESC')
                        ->get();
        }else {
            $search = $request->input('search.value');
            $posts =  DB::table('packages')->select('packages.packeg_id','packages.supplier_tracking_no','packages.tracking_no','packages.invoice','packages.manifest_no','packages.courier','packages.weight','packages.item_description','packages.user_name','packages.status_name','packages.created_at')
                        //->join('users', 'packages.mailbox', '=', 'users.mailbox')
                        //->join('package_status', 'package_status.id', '=', 'packages.status')
                        ->where('packages.is_temprary_logs' , $is_temprary_logs)
                        ->where('packages.status' ,  1)
                        ->where('packages.user_name', 'LIKE',"%{$search}%")
                        ->orWhere('packages.manifest_no','LIKE',"%{$search}%")
                        ->orWhere('packages.supplier_tracking_no','LIKE',"%{$search}%")
                        ->orWhere('packages.status_name','LIKE',"%{$search}%")
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy('packages.created_at','DESC')
                        ->get();
            $totalFiltered = DB::table('packages')
                                ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                                //->join('package_status', 'package_status.id', '=', 'packages.status')
                                //->join('outlets', 'outlets.outlet_id', '=', 'users.outlet_id','left')
                                ->where('packages.is_temprary_logs' , $is_temprary_logs)
                                ->where('packages.status' ,  1)
                                ->where('packages.user_name', 'LIKE',"%{$search}%")
                                ->orWhere('packages.manifest_no','LIKE',"%{$search}%")
                                ->orWhere('packages.supplier_tracking_no','LIKE',"%{$search}%")
                                ->orWhere('packages.status_name','LIKE',"%{$search}%")
                                ->count();
                                //return $totalFiltered;
        }

        $data = array();
        if(!empty($posts))
        {
            foreach ($posts as $post)
            {
                $nestedData['manifest_no'] = $post->manifest_no;
                $nestedData['supplier_tracking_no'] = $post->supplier_tracking_no;
                $nestedData['courier'] = $post->courier;
                $nestedData['item_description'] = substr(strip_tags($post->item_description),0,50)."...";
                $nestedData['first_name'] = $post->user_name;
                $nestedData['name'] = $post->status_name;
                $nestedData['created_at'] = date('j M Y h:i a',strtotime($post->created_at));
                $data[] = $nestedData;
            }
        }
        //dd($data);
        $json_data = array(
                        "draw"            => intval($request->input('draw')),
                        "recordsTotal"    => intval($totalData),
                        "recordsFiltered" => intval($totalFiltered),
                        "data"            => $data
                    );
        echo json_encode($json_data);
    }

    public function listViewRecent(){
        $query = DB::table('packages')
                    ->select('packages.*','users.first_name','users.last_name','package_status.name')
                    ->join('users', 'packages.mailbox', '=', 'users.mailbox')
                    ->join('package_status', 'package_status.id', '=', 'packages.status')
                    ->where('packages.status' ,'!=' , 1);
        return DataTables::of($query)
            ->editColumn('weight', fn ($data) => $this->packageMeasureDisplay($data))
            ->make(true);
    }

    private function packageMeasureDisplay($package): string
    {
        if(stripos((string) ($package->freight_type ?? ''), 'sea') !== false){
            $cuft = (float) (($package->volume_cuft ?? 0) ?: ($package->chargeable_weight ?? 0));
            $cbm = (float) (($package->volume_cbm ?? 0) ?: ($cuft > 0 ? $cuft / 35.3147 : 0));

            return number_format($cuft, 2).' cu.ft / '.number_format($cbm, 3).' CBM';
        }

        $unit = strtolower((string) ($package->weight_unit ?? 'lb')) === 'kg' ? 'kg' : 'lb';

        return number_format((float) ($package->weight ?? 0), 2).' '.$unit;
    }

    public function profile(Request $request) {
        $data['title'] = 'Manages Profile';
        $data['class'] = 'dashboard';
        $data['table'] = 'Update Profile';
        $user_id = Session::get('user_id');
        if ($request->isMethod('get')) {
            $data['admins'] = User::where('user_id', $user_id)->first();
            return view('admin/dashboard/create' , $data);
        }else{
            if ($request->hasFile('image')) {
                $image_name = '';
                $admin_img = User::where('user_id', $user_id)->first();
                if($admin_img){
                    $avater = $admin_img->image;
                    @unlink(base_path() . '/users/' . $avater);
                }

                $imageTempName = $request->file('image')->getPathname();
                $venue_img_extension = $request->image->extension();
                $imageName = uniqid() . '.' . $venue_img_extension;
                $path = base_path() . '/public/users/';
                $request->file('image')->move($path, $imageName);
                $image = $imageName;
            } else {
                $image = '';
                $admin_img = User::where('user_id', $user_id)->first();
                if($admin_img){
                    $image = $admin_img->image;
                }
            }
            User::where('user_id', $user_id)
                    ->update([
                            'first_name' => $request->first_name,
                            'last_name' => $request->last_name,
                            'image' => $image
                        ]);
            Session::put('admin_name', $request->first_name);
            Session::put('avater', $image);
            Session::flash('success_msg', 'Profile has been updated successfully.');
            return redirect('admin/profile');
        }
    }

    public function settings(Request $request) {
        $data['title'] = 'Manages Settings';
        $data['class'] = 'settings';
        $data['table'] = 'Manages Settings';
        $user_id = Session::get('user_id');
        if ($request->isMethod('get')) {
                $data['setting'] = Setting::first();
                return view('admin/settings/edit' , $data);
        }else{
            if ($request->hasFile('site_logo')) {
                $image_name = '';
                $sitelogo = Setting::first();
                if($sitelogo){
                    $site_logo = $sitelogo->site_logo;
                    @unlink(base_path() . '/public/settings/' . $site_logo);
                }

                $imageTempName = $request->file('site_logo')->getPathname();
                $venue_img_extension = $request->site_logo->extension();
                $imageName = uniqid() . '.' . $venue_img_extension;
                $path = base_path() . '/public/settings/';
                $request->file('site_logo')->move($path, $imageName);
                $image = $imageName;
            } else {
                $sitelogo = Setting::first();
                if($sitelogo){
                    $image = $sitelogo->site_logo;
                }
            }

            Setting::where('id', 1)
                ->update([
                    'site_name' => $request->site_name,
                    'site_title' => $request->site_title,
                    'site_email' => $request->site_email,
                    'from_email' => $request->from_email,
                    'phone' => $request->phone,
                    'site_footer_text' => $request->site_footer_text,
                    'us_rate' => $request->us_rate,
                    'package_shipping_address_1' => $request->package_shipping_address_1,
                    'package_shipping_address_2' => $request->package_shipping_address_2,
                    'city' => $request->city,
                    'state' => $request->state,
                    'zip' => $request->zip,
                    'country' => $request->country,
                    'local_address' => $request->local_address,
                    'sea_shipping_address_1' => $request->sea_shipping_address_1,
                    'sea_shipping_address_2' => $request->sea_shipping_address_2,
                    'sea_city' => $request->sea_city,
                    'sea_state' => $request->sea_state,
                    'sea_zip' => $request->sea_zip,
                    'sea_country' => $request->sea_country,
                    'sea_local_address' => $request->sea_local_address,

                    'site_logo' => $image
                ]);
            Session::flash('success_msg', 'Setting has been updated successfully.');
            return redirect('admin/settings');
        }
    }

    public function changePassword(Request $request) {
        $data['title'] = 'Change Password';
        $data['class'] = 'users';
        $data['table'] = 'Change Password';
        $user_id = Session::get('user_id');
        if ($request->isMethod('get')) {
            //$data['email'] = $id;
            return view('admin/users/change_password', $data);
        }else{
            $validator = Validator::make($request->all(), [
                'new_password' => 'required|same:confirm_password',
                'confirm_password' => 'required',
            ]);
            if ($validator->fails()) {
                return redirect('admin/change/password')
                        ->withInput()
                        ->withErrors($validator);
            }else{
                $users = User::where('user_id', $user_id)->first();
                if($users){
                    $user_password = $users->password;
                    if(Hash::check($request->old_password, $user_password)){
                        $password = $request->input("new_password");
                        User::where('user_id', $user_id)->update(['password' => Hash::make($password)]);
                        Session::flash('error_msg', 'Your password was reset successfully. You may now log in with new password.');
                        $request->session()->flush();
                        return redirect('admin/change/password');
                    }else{
                        Session::flash('error_msg', 'Your current password does not matched');
                        return redirect('admin/change/password');
                    }
                }else{
                    Session::flash('error_msg', 'User does not exist in our database.');
                    return redirect('admin/change/password');
                }
            }
        }
    }

    public function sendEmail(Request $request){
        $data['title'] = 'Send Email';
        $data['class'] = 'email';
        $data['table'] = 'Send Email';
        if ($request->isMethod('get')) {
            //$data['users'] = User::where('role_id' , 0)->orderby('first_name','ASC')->get();
            $data['couriers'] = Courier::get();
            return view('admin/send_emails/send_email' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                                'subject' => 'required',
                                'description' => 'required'
                            ]);
            if ($validator->fails()) {
                    return redirect('send/email')
                                    ->withInput()
                                    ->withErrors($validator);
            }else{
                $settings = DB::table('settings')->first();
                if($settings){
                    $email_from = $settings->from_email;
                }else{
                    //$email_from = 'onepluscourierja@gmail.com';
                }
                //return $email_from;
                $company_name = $settings->company_name;
                $logo = URL::to('/') . '/public/settings/'.$settings->site_logo;
                $obj['email_from'] = $email_from;
                $obj['subject'] = $request->subject;
                $obj['decription'] = $request->description;
                $customer_id = $request->user_id;
                $is_user = $request->is_user;
                if($is_user == 'All'){
                    $users = Courier::get();
                }else{
                    $users = Courier::whereIn('id' , $customer_id)->get();
                }
                if(count($users) > 0){
                    foreach($users as $row){
                        $this->send_email_shop($row->email, $row->name, $request->subject, $email_from, $obj,$logo,$company_name,'send_email');
                    }
                }
                Session::flash('success_msg', 'Send email to couriers successfully');
                return redirect('send/email');
            }
        }
    }

    public function send_email_shop($email, $user_name, $email_subject, $email_from, $link,$logo,$company_name,$view_name) {
        $res['name'] = $user_name;
        $res['company_name'] = $company_name;
        $res['subject'] = $link['subject'];
        $res['decription'] = $link['decription'];
        $res['logo_url'] = $logo;
        Mail::send('email/'.$view_name , $res, function ($message) use ($email_from, $email, $user_name, $email_subject,$company_name) {
            $message->from($email_from, $company_name);
            $message->to($email, $user_name)->subject($email_subject);
            //$message->to('rizwangill132@gmail.com', $user_name)->subject($email_subject);
        });
    }

    public function unpaidInvoice(){
        $outstanding_balance = DB::table('packages')->select('is_invoice','created_at')->where('courier_id' , 4)->whereDate('created_at' , '<=' , '2022-07-09')->get();
        dd(count($outstanding_balance));
        if(count($outstanding_balance) > 0){
            foreach($outstanding_balance as $row){
                $totalInv = commonHelper::getInvoiceOutstanding($row->invoice_id);
                DB::table('invoice_packages')->where('invoice_id' , $row->invoice_id)->update(['status' => 0]);
            }
        }
        return 'invoice updated';
    }

    public function updateUserName(){
        $allUsers = DB::table('users')->select('mailbox','first_name','last_name')->limit(500)->where('is_update' , 0)->get();
        //dd($allUsers);
        if(count($allUsers) > 0){
            foreach($allUsers as $row){
                $user_name = $row->first_name .' '.$row->last_name;
                DB::table('packages')->where('mailbox' , $row->mailbox)->update(['user_name' => $user_name]);
                DB::table('users')->where('mailbox' , $row->mailbox)->update(['is_update' => 1]);
            }
        }
        return 'User name updated';
    }

    public function updateInvoiceName(){
        $allUsers = DB::table('users')->select('mailbox','first_name','last_name')->limit(500)->where('is_update' , 1)->get();
        //dd($allUsers);
        if(count($allUsers) > 0){
            foreach($allUsers as $row){
                $user_name = $row->first_name .' '.$row->last_name;
                DB::table('invoices')->where('mailbox_no' , $row->mailbox)->update(['user_name' => $user_name]);
                DB::table('users')->where('mailbox' , $row->mailbox)->update(['is_update' => 0]);
            }
        }
        return 'User name updated';
    }

    public function updatePackageStatusName(){
        $allStatus = DB::table('package_status')->select('id','name')->get();
        //dd($allStatus);
        if(count($allStatus) > 0){
            foreach($allStatus as $row){
                DB::table('packages')->where('status' , $row->id)->update(['status_name' => $row->name]);
            }
        }
        return 'Package status updated';
    }
}
