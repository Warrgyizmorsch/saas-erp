<?php

namespace Modules\CRM\App\Exports;

use Modules\CRM\App\Models\Leads;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeadsExcelExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Leads::with(['user', 'owner']);

        // SEARCH
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $digitsOnly = preg_replace('/\D+/', '', $search); // keep only numbers

            $query->whereHas('user', function ($q) use ($search, $digitsOnly) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");

                // If user typed something that looks like a phone number
                if ($digitsOnly !== '') {
                    // Compare against contact_no with spaces removed
                    $q->orWhereRaw(
                        "REPLACE(contact_no, ' ', '') LIKE ?",
                        ['%' . $digitsOnly . '%']
                    );
                } else {
                    // Fallback: plain like on contact_no
                    $q->orWhere('contact_no', 'like', "%{$search}%");
                }
            });
        }

        // SOURCE
        if (!empty($this->filters['source'])) {
            $query->where('platform', $this->filters['source']);
        }

        // STATUS
        if (!empty($this->filters['lead_engagement_status'])) {
            $query->where('lead_engagement_status', $this->filters['lead_engagement_status']);
        }

        // DATE RANGE

        if (!empty($this->filters['from']) && !empty($this->filters['to'])) {

            $from = Carbon::parse($this->filters['from'])->startOfDay();
            $to   = Carbon::parse($this->filters['to'])->endOfDay();

            $query->whereBetween('created_at', [$from, $to]);
        }

        // OWNER
        if (!empty($this->filters['owner_id'])) {

            if ($this->filters['owner_id'] == 'null') {
                $query->whereNull('lead_owner');
            } else {
                $query->where('lead_owner', $this->filters['owner_id']);
            }
        }

        if (!empty($this->filters['bucket_id'])) {
            $query->where('lead_bucket_id', $this->filters['bucket_id']);
        }

        return $query->get()->map(function ($lead) {
            return [
                'User Name'   => $lead->user->name ?? 'N/A',
                'User Email'  => $lead->user->email ?? 'N/A',
                'Contact no'  => $lead->user->contact_no ?? 'N/A',
                'Lead Owner'  => $lead->owner->name ?? 'N/A',
                'Lead Status' => $lead->lead_status,
                'Lead Bucket' => $lead->bucket->name,
                'Platform'    => $lead->platform,
                'Created Date' => $lead->created_at?->format('d-m-Y'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'User Name',
            'User Email',
            'Contact no',
            'Lead Owner',
            'Lead Status',
            'Lead Bucket',
            'Platform',
            'Created Date'
        ];
    }
}

