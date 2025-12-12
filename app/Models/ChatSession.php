<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_sessions';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the session data as an array.
     */
    public function getSessionData(): array
    {
        return $this->data ?? [];
    }

    /**
     * Set the session data.
     */
    public function setSessionData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Update session data.
     */
    public function updateSessionData(array $newData): void
    {
        $currentData = $this->getSessionData();
        $this->data = array_merge($currentData, $newData);
    }

    /**
     * Get session by user ID.
     */
    public static function findByUserId(string $userId): ?self
    {
        return static::where('user_id', $userId)->first();
    }

    /**
     * Create or update a session for a user.
     */
    public static function createOrUpdate(string $userId, array $data): self
    {
        $session = static::findByUserId($userId);
        
        if ($session) {
            $session->updateSessionData($data);
            $session->save();
        } else {
            $session = new static([
                'user_id' => $userId,
                'data' => $data
            ]);
            $session->save();
        }
        
        return $session;
    }

    /**
     * Clear session data for a user.
     */
    public static function clear(string $userId): bool
    {
        $session = static::findByUserId($userId);
        
        if ($session) {
            $session->data = [];
            return $session->save();
        }
        
        return false;
    }

    /**
     * Get all active sessions (updated within last X hours).
     */
    public static function getActiveSessions(int $hours = 24)
    {
        return static::where('updated_at', '>=', now()->subHours($hours))
                    ->orderBy('updated_at', 'desc')
                    ->get();
    }

    /**
     * Clean up old sessions.
     */
    public static function cleanupOldSessions(int $days = 30): int
    {
        return static::where('updated_at', '<', now()->subDays($days))
                    ->delete();
    }
}