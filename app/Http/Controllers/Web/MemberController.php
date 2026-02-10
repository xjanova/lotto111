<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use App\Models\LotteryRound;
use App\Models\LotteryType;
use App\Models\User;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function dashboard(): View
    {
        $user = Auth::user();

        // Stats
        $totalDeposits = $user->deposits()->where('status', 'credited')->sum('amount');
        $totalWithdrawals = $user->withdrawals()->whereIn('status', ['approved', 'completed'])->sum('amount');
        $totalBets = abs($user->transactions()->where('type', 'bet')->sum('amount'));
        $totalWins = $user->transactions()->where('type', 'win')->sum('amount');
        $totalTickets = $user->tickets()->count();
        $pendingWithdrawals = $user->withdrawals()->where('status', 'pending')->sum('amount');

        // Recent transactions
        $recentTransactions = $user->transactions()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Today's tickets
        $todayTickets = $user->tickets()
            ->with('lotteryRound.lotteryType')
            ->whereDate('bet_at', today())
            ->orderByDesc('bet_at')
            ->limit(10)
            ->get();

        // Open rounds
        $openRounds = LotteryRound::with('lotteryType')
            ->where('status', 'open')
            ->orderBy('close_at')
            ->limit(6)
            ->get();

        // Affiliate
        $referralCount = User::where('referred_by', $user->id)->count();
        $pendingCommission = AffiliateCommission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('commission');

        return view('member.dashboard', compact(
            'user',
            'totalDeposits',
            'totalWithdrawals',
            'totalBets',
            'totalWins',
            'totalTickets',
            'pendingWithdrawals',
            'recentTransactions',
            'todayTickets',
            'openRounds',
            'referralCount',
            'pendingCommission',
        ));
    }

    public function lottery(): View
    {
        // Mockup data - lottery types
        $lotteryTypes = [
            ['id' => 1, 'name' => 'หวยรัฐบาลไทย', 'icon' => '🇹🇭', 'color' => '#4f46e5', 'rounds_count' => 1],
            ['id' => 2, 'name' => 'หวยลาว', 'icon' => '🇱🇦', 'color' => '#dc2626', 'rounds_count' => 2],
            ['id' => 3, 'name' => 'หวยฮานอย', 'icon' => '🇻🇳', 'color' => '#ea580c', 'rounds_count' => 3],
            ['id' => 4, 'name' => 'หวยมาเลย์', 'icon' => '🇲🇾', 'color' => '#0d9488', 'rounds_count' => 1],
            ['id' => 5, 'name' => 'หวยยี่กี', 'icon' => '🎲', 'color' => '#7c3aed', 'rounds_count' => 88],
            ['id' => 6, 'name' => 'หวยหุ้นไทย', 'icon' => '📈', 'color' => '#2563eb', 'rounds_count' => 4],
        ];

        // Mockup open rounds
        $openRounds = [
            ['id' => 1, 'type_id' => 1, 'name' => 'งวด 16 ก.พ. 2569', 'close_at' => '14:30'],
            ['id' => 2, 'type_id' => 2, 'name' => 'หวยลาว งวดเย็น', 'close_at' => '20:00'],
            ['id' => 3, 'type_id' => 2, 'name' => 'หวยลาวพิเศษ', 'close_at' => '20:30'],
            ['id' => 4, 'type_id' => 3, 'name' => 'ฮานอยปกติ', 'close_at' => '18:10'],
            ['id' => 5, 'type_id' => 3, 'name' => 'ฮานอยพิเศษ', 'close_at' => '18:30'],
            ['id' => 6, 'type_id' => 3, 'name' => 'ฮานอย VIP', 'close_at' => '19:00'],
            ['id' => 7, 'type_id' => 4, 'name' => 'มาเลย์ 4D', 'close_at' => '19:00'],
            ['id' => 8, 'type_id' => 5, 'name' => 'ยี่กี รอบที่ 42', 'close_at' => '15:05'],
            ['id' => 9, 'type_id' => 5, 'name' => 'ยี่กี รอบที่ 43', 'close_at' => '15:20'],
            ['id' => 10, 'type_id' => 6, 'name' => 'หุ้นไทย เที่ยง', 'close_at' => '12:00'],
            ['id' => 11, 'type_id' => 6, 'name' => 'หุ้นไทย บ่าย', 'close_at' => '14:30'],
        ];

        // Recent results mockup
        $recentResults = [
            ['type' => 'หวยรัฐบาลไทย', 'number' => '835792', 'date' => '1 ก.พ. 2569'],
            ['type' => 'หวยลาว', 'number' => '4281', 'date' => '10 ก.พ. 2569'],
            ['type' => 'หวยฮานอย', 'number' => '67294', 'date' => '9 ก.พ. 2569'],
        ];

        return view('member.lottery.index', compact('lotteryTypes', 'openRounds', 'recentResults'));
    }

    public function tickets(): View
    {
        $stats = [
            'total_bets' => 48,
            'total_wins' => 5,
            'total_win_amount' => 12750,
        ];

        $tickets = [
            [
                'type_name' => 'หวยรัฐบาลไทย', 'round_name' => 'งวด 1 ก.พ. 2569',
                'color' => '#4f46e5', 'status' => 'won', 'date' => '1 ก.พ. 2569 12:30',
                'total_amount' => 200, 'win_amount' => 6000,
                'numbers' => [
                    ['bet_type' => '3 ตัวบน', 'number' => '835', 'amount' => 100, 'won' => true, 'win_amount' => 5000],
                    ['bet_type' => '2 ตัวล่าง', 'number' => '92', 'amount' => 100, 'won' => true, 'win_amount' => 1000],
                ],
            ],
            [
                'type_name' => 'หวยฮานอย', 'round_name' => 'ฮานอยปกติ 9 ก.พ. 2569',
                'color' => '#ea580c', 'status' => 'lost', 'date' => '9 ก.พ. 2569 18:00',
                'total_amount' => 150, 'win_amount' => 0,
                'numbers' => [
                    ['bet_type' => '3 ตัวบน', 'number' => '123', 'amount' => 50, 'won' => false, 'win_amount' => 0],
                    ['bet_type' => '2 ตัวบน', 'number' => '45', 'amount' => 50, 'won' => false, 'win_amount' => 0],
                    ['bet_type' => 'วิ่งบน', 'number' => '7', 'amount' => 50, 'won' => false, 'win_amount' => 0],
                ],
            ],
            [
                'type_name' => 'หวยยี่กี', 'round_name' => 'ยี่กี รอบที่ 40',
                'color' => '#7c3aed', 'status' => 'pending', 'date' => '10 ก.พ. 2569 14:30',
                'total_amount' => 300, 'win_amount' => 0,
                'numbers' => [
                    ['bet_type' => '3 ตัวโต๊ด', 'number' => '567', 'amount' => 100, 'won' => false, 'win_amount' => 0],
                    ['bet_type' => '2 ตัวบน', 'number' => '89', 'amount' => 100, 'won' => false, 'win_amount' => 0],
                    ['bet_type' => '2 ตัวล่าง', 'number' => '34', 'amount' => 100, 'won' => false, 'win_amount' => 0],
                ],
            ],
            [
                'type_name' => 'หวยลาว', 'round_name' => 'หวยลาวเย็น 10 ก.พ.',
                'color' => '#dc2626', 'status' => 'pending', 'date' => '10 ก.พ. 2569 19:50',
                'total_amount' => 100, 'win_amount' => 0,
                'numbers' => [
                    ['bet_type' => '3 ตัวบน', 'number' => '999', 'amount' => 50, 'won' => false, 'win_amount' => 0],
                    ['bet_type' => 'วิ่งล่าง', 'number' => '5', 'amount' => 50, 'won' => false, 'win_amount' => 0],
                ],
            ],
        ];

        return view('member.tickets', compact('stats', 'tickets'));
    }

    public function deposit(): View
    {
        $balance = Auth::user()->balance;

        $bankAccounts = [
            ['bank' => 'ธนาคารกสิกรไทย', 'name' => 'บจก. ลอตโต้ เอ็นเตอร์ไพรส์', 'number' => '068-8-12345-6', 'color' => '#00A650'],
            ['bank' => 'ธนาคารไทยพาณิชย์', 'name' => 'บจก. ลอตโต้ เอ็นเตอร์ไพรส์', 'number' => '405-6-78901-2', 'color' => '#4E2A82'],
            ['bank' => 'ธนาคารกรุงเทพ', 'name' => 'บจก. ลอตโต้ เอ็นเตอร์ไพรส์', 'number' => '123-4-56789-0', 'color' => '#1E3A8A'],
        ];

        $recentDeposits = [
            ['amount' => 1000, 'status' => 'credited', 'date' => '10 ก.พ. 2569 14:32'],
            ['amount' => 500, 'status' => 'credited', 'date' => '8 ก.พ. 2569 09:15'],
            ['amount' => 2000, 'status' => 'pending', 'date' => '10 ก.พ. 2569 15:00'],
            ['amount' => 300, 'status' => 'rejected', 'date' => '5 ก.พ. 2569 11:20'],
        ];

        return view('member.deposit', compact('balance', 'bankAccounts', 'recentDeposits'));
    }

    public function withdrawal(): View
    {
        $balance = Auth::user()->balance;
        $pendingAmount = 500.00;

        $userBankAccounts = [
            ['id' => 1, 'bank_name' => 'ธนาคารกสิกรไทย', 'account_number' => '068-8-xxxxx-6', 'account_name' => Auth::user()->name, 'color' => '#00A650'],
            ['id' => 2, 'bank_name' => 'ธนาคารไทยพาณิชย์', 'account_number' => '405-6-xxxxx-2', 'account_name' => Auth::user()->name, 'color' => '#4E2A82'],
        ];

        $recentWithdrawals = [
            ['amount' => 5000, 'status' => 'completed', 'bank' => 'กสิกรไทย', 'date' => '9 ก.พ. 2569 10:45'],
            ['amount' => 500, 'status' => 'pending', 'bank' => 'ไทยพาณิชย์', 'date' => '10 ก.พ. 2569 13:20'],
            ['amount' => 2000, 'status' => 'completed', 'bank' => 'กสิกรไทย', 'date' => '7 ก.พ. 2569 16:00'],
            ['amount' => 1000, 'status' => 'rejected', 'bank' => 'กสิกรไทย', 'date' => '3 ก.พ. 2569 09:30'],
        ];

        return view('member.withdrawal', compact('balance', 'pendingAmount', 'userBankAccounts', 'recentWithdrawals'));
    }

    public function results(): View
    {
        $lotteryTypes = [
            ['id' => 'thai', 'name' => 'รัฐบาล', 'icon' => '🇹🇭'],
            ['id' => 'lao', 'name' => 'ลาว', 'icon' => '🇱🇦'],
            ['id' => 'hanoi', 'name' => 'ฮานอย', 'icon' => '🇻🇳'],
            ['id' => 'yeekee', 'name' => 'ยี่กี', 'icon' => '🎲'],
            ['id' => 'stock', 'name' => 'หุ้นไทย', 'icon' => '📈'],
        ];

        $results = [
            '10 กุมภาพันธ์ 2569' => [
                [
                    'type_name' => 'หวยฮานอย', 'round_name' => 'ฮานอยปกติ', 'icon' => '🇻🇳', 'time' => '18:15',
                    'prizes' => [
                        ['name' => '3 ตัวบน', 'number' => '672', 'highlight' => true],
                        ['name' => '2 ตัวล่าง', 'number' => '94', 'highlight' => true],
                        ['name' => 'วิ่งบน', 'number' => '6', 'highlight' => false],
                    ],
                ],
                [
                    'type_name' => 'หวยลาว', 'round_name' => 'ลาวเย็น', 'icon' => '🇱🇦', 'time' => '20:30',
                    'prizes' => [
                        ['name' => '3 ตัวบน', 'number' => '428', 'highlight' => true],
                        ['name' => '2 ตัวล่าง', 'number' => '81', 'highlight' => true],
                        ['name' => 'วิ่งล่าง', 'number' => '1', 'highlight' => false],
                    ],
                ],
                [
                    'type_name' => 'หวยยี่กี', 'round_name' => 'ยี่กี รอบ 41', 'icon' => '🎲', 'time' => '14:40',
                    'prizes' => [
                        ['name' => '3 ตัวบน', 'number' => '159', 'highlight' => true],
                        ['name' => '2 ตัวล่าง', 'number' => '37', 'highlight' => true],
                    ],
                ],
            ],
            '9 กุมภาพันธ์ 2569' => [
                [
                    'type_name' => 'หวยฮานอย', 'round_name' => 'ฮานอยปกติ', 'icon' => '🇻🇳', 'time' => '18:15',
                    'prizes' => [
                        ['name' => '3 ตัวบน', 'number' => '501', 'highlight' => true],
                        ['name' => '2 ตัวล่าง', 'number' => '26', 'highlight' => true],
                    ],
                ],
                [
                    'type_name' => 'หุ้นไทย', 'round_name' => 'หุ้นไทยเที่ยง', 'icon' => '📈', 'time' => '12:15',
                    'prizes' => [
                        ['name' => '3 ตัวบน', 'number' => '847', 'highlight' => true],
                        ['name' => '2 ตัวล่าง', 'number' => '63', 'highlight' => true],
                    ],
                ],
            ],
            '1 กุมภาพันธ์ 2569' => [
                [
                    'type_name' => 'หวยรัฐบาลไทย', 'round_name' => 'งวด 1 ก.พ. 2569', 'icon' => '🇹🇭', 'time' => '15:00',
                    'prizes' => [
                        ['name' => 'รางวัลที่ 1', 'number' => '835792', 'highlight' => true],
                        ['name' => '3 ตัวบน', 'number' => '835', 'highlight' => true],
                        ['name' => '3 ตัวโต๊ด', 'number' => '835', 'highlight' => false],
                        ['name' => '2 ตัวล่าง', 'number' => '92', 'highlight' => true],
                    ],
                ],
            ],
        ];

        return view('member.results', compact('lotteryTypes', 'results'));
    }

    public function transactions(): View
    {
        $totalIncome = 18750.00;
        $totalExpense = 12350.00;

        $transactions = [
            ['type' => 'win', 'amount' => 6000, 'balance_after' => 9500, 'description' => 'ถูกรางวัล 3 ตัวบน #835', 'date' => '10 ก.พ. 2569 15:05'],
            ['type' => 'bet', 'amount' => 300, 'balance_after' => 3500, 'description' => 'ยี่กี รอบที่ 40', 'date' => '10 ก.พ. 2569 14:30'],
            ['type' => 'deposit', 'amount' => 1000, 'balance_after' => 3800, 'description' => 'เติมเงินผ่านธนาคาร', 'date' => '10 ก.พ. 2569 14:32'],
            ['type' => 'bet', 'amount' => 200, 'balance_after' => 2800, 'description' => 'หวยรัฐบาลไทย งวด 16 ก.พ.', 'date' => '10 ก.พ. 2569 12:30'],
            ['type' => 'commission', 'amount' => 150, 'balance_after' => 3000, 'description' => 'ค่าแนะนำจาก สมชาย', 'date' => '9 ก.พ. 2569 20:15'],
            ['type' => 'withdraw', 'amount' => 5000, 'balance_after' => 2850, 'description' => 'ถอนเงิน กสิกรไทย', 'date' => '9 ก.พ. 2569 10:45'],
            ['type' => 'win', 'amount' => 1000, 'balance_after' => 7850, 'description' => 'ถูกรางวัล 2 ตัวล่าง #92', 'date' => '8 ก.พ. 2569 18:20'],
            ['type' => 'bet', 'amount' => 150, 'balance_after' => 6850, 'description' => 'หวยฮานอยปกติ', 'date' => '8 ก.พ. 2569 17:50'],
            ['type' => 'deposit', 'amount' => 500, 'balance_after' => 7000, 'description' => 'เติมเงินผ่าน TrueWallet', 'date' => '8 ก.พ. 2569 09:15'],
            ['type' => 'bonus', 'amount' => 100, 'balance_after' => 6500, 'description' => 'โบนัสต้อนรับสมาชิกใหม่', 'date' => '7 ก.พ. 2569 10:00'],
            ['type' => 'bet', 'amount' => 500, 'balance_after' => 6400, 'description' => 'หวยลาวเย็น', 'date' => '7 ก.พ. 2569 19:00'],
            ['type' => 'deposit', 'amount' => 2000, 'balance_after' => 6900, 'description' => 'เติมเงินผ่านธนาคาร', 'date' => '7 ก.พ. 2569 16:00'],
        ];

        return view('member.transactions', compact('totalIncome', 'totalExpense', 'transactions'));
    }

    public function notifications(): View
    {
        $unreadCount = 3;

        $notifications = [
            ['type' => 'win', 'title' => 'ยินดีด้วย! คุณถูกรางวัล', 'message' => 'เลข 835 ถูกรางวัล 3 ตัวบน ได้รับเงินรางวัล ฿5,000', 'time' => '10 นาทีที่แล้ว', 'read' => false],
            ['type' => 'deposit', 'title' => 'เติมเงินสำเร็จ', 'message' => 'เติมเงิน ฿1,000 เข้าสู่บัญชีเรียบร้อยแล้ว ยอดคงเหลือ ฿9,500', 'time' => '30 นาทีที่แล้ว', 'read' => false],
            ['type' => 'result', 'title' => 'ผลหวยฮานอยออกแล้ว', 'message' => 'ผลหวยฮานอยปกติ ประจำวันที่ 10 ก.พ. 2569: 3 ตัวบน 672 / 2 ตัวล่าง 94', 'time' => '2 ชั่วโมงที่แล้ว', 'read' => false],
            ['type' => 'promotion', 'title' => 'โปรโมชั่นพิเศษ!', 'message' => 'เติมเงินวันนี้ รับโบนัสเพิ่ม 10% สูงสุด ฿500 ถึง 28 ก.พ. 2569', 'time' => '5 ชั่วโมงที่แล้ว', 'read' => true],
            ['type' => 'withdrawal', 'title' => 'ถอนเงินสำเร็จ', 'message' => 'ถอนเงิน ฿5,000 ไปยังบัญชีกสิกรไทย เรียบร้อยแล้ว', 'time' => 'เมื่อวาน', 'read' => true],
            ['type' => 'system', 'title' => 'ปรับปรุงระบบ', 'message' => 'ระบบจะปิดปรับปรุงชั่วคราว วันที่ 15 ก.พ. 2569 เวลา 02:00-04:00', 'time' => '2 วันที่แล้ว', 'read' => true],
            ['type' => 'result', 'title' => 'ผลหวยรัฐบาลออกแล้ว', 'message' => 'ผลหวยรัฐบาลไทย งวด 1 ก.พ. 2569: รางวัลที่ 1 = 835792', 'time' => '9 วันที่แล้ว', 'read' => true],
            ['type' => 'promotion', 'title' => 'สมาชิกใหม่ รับโบนัส ฿100', 'message' => 'ยินดีต้อนรับ! คุณได้รับโบนัสสมาชิกใหม่ ฿100 แล้ว', 'time' => '10 วันที่แล้ว', 'read' => true],
        ];

        return view('member.notifications', compact('unreadCount', 'notifications'));
    }

    public function referral(): View
    {
        $user = Auth::user();
        $affiliate = app(AffiliateService::class)->getDashboard($user);
        $referrals = User::where('referred_by', $user->id)
            ->select('id', 'name', 'phone', 'created_at', 'last_login_at')
            ->orderByDesc('created_at')
            ->paginate(20);

        $commissions = AffiliateCommission::where('user_id', $user->id)
            ->with('fromUser:id,name')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('member.referral', compact('user', 'affiliate', 'referrals', 'commissions'));
    }

    public function withdrawCommission(Request $request)
    {
        $user = Auth::user();
        $result = app(AffiliateService::class)->withdrawCommissions($user);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function profile(): View
    {
        $user = Auth::user();
        $user->load('primaryBankAccount', 'bankAccounts');

        return view('member.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'line_id' => 'nullable|string|max:100',
        ]);

        Auth::user()->update($validated);

        return response()->json(['success' => true, 'message' => 'อัปเดตข้อมูลสำเร็จ']);
    }
}
