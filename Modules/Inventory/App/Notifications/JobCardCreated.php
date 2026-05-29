<?php

namespace Modules\Inventory\App\Notifications;

use Modules\Inventory\App\Models\JobCard;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class JobCardCreated extends Notification
{
    use Queueable;

    public JobCard $jobCard;

    public function __construct(JobCard $jobCard)
    {
        $this->jobCard = $jobCard;
    }

    // kaha store hogi
    public function via($notifiable)
    {
        return ['database'];
    }

    // notifications table ka `data` column
    public function toDatabase($notifiable)
    {
        return [
            'module'      => 'job_card',
            'job_card_id' => $this->jobCard->id,
            'job_card_no' => $this->jobCard->job_card_no,
            'message'     => 'New Job Card Created',
            'created_by'  => auth()->id(),
            'auth'        =>auth()->user()->name ?? 'System',
        ];
    }
}
