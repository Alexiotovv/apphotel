<?php

namespace App\Console\Commands;

use App\Models\ChatSession;
use Illuminate\Console\Command;

class CleanupChatSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:sessions:cleanup {--days=30 : Days to keep sessions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old chat sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        
        $this->info("Cleaning up chat sessions older than {$days} days...");
        
        $deleted = ChatSession::cleanupOldSessions($days);
        
        $this->info("Deleted {$deleted} old chat sessions.");
        
        if ($deleted > 0) {
            $this->info("Cleanup completed successfully.");
        } else {
            $this->info("No old sessions to clean up.");
        }
        
        return 0;
    }
}