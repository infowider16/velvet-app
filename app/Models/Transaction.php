<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\GhostManagement;
use App\Models\Boost;
use App\Models\Pin;
use Carbon\Carbon;

class Transaction extends Model

{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transactions';
    public $timestamps = true;
    protected $fillable = [
        'transaction_id',
        'plan_id',
        'user_id',
        'type',
        'start_time',
        'end_time',
        'boost_count',
        'pin_count',
        'amount',
        'currency',
        'payment_status',
        'platform',
        'status',
        'ghost_expire'
    ];
    
    protected $casts = [
        'start_time' => 'datetime:Y-m-d H:i:s',
        'end_time'   => 'datetime:Y-m-d H:i:s',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Explicit relations so we can eager-load all and pick by type
    public function ghostPlan()
    {
        return $this->belongsTo(GhostManagement::class, 'plan_id');
    }

    public function boostPlan()
    {
        return $this->belongsTo(Boost::class, 'plan_id');
    }

    public function pinPlan()
    {
        return $this->belongsTo(Pin::class, 'plan_id');
    }

    
}
