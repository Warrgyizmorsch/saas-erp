<?php

namespace Modules\CRM\App\Exports;

use Modules\CRM\App\Models\Leads;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeadsExport implements FromCollection, WithHeadings
{
    protected $filters;
    protected $selectedColumns;

    // ✅ Max number of rows to prevent timeout
    protected $maxRows = 10000; 

    public function __construct($filters, $selectedColumns)
    {
        $this->filters = $filters;
        $this->selectedColumns = $selectedColumns;
    }

    public function collection()
    {
        $query = Leads::with(['user', 'owner', 'bucket', 'attributes']);

        // Role restriction
        if (auth()->user()->role_id == 3) {
            $query->where('lead_owner', auth()->id());
        }

        // 🔍 Search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_no', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 📅 Date filters
        if (!empty($this->filters['from']) && !empty($this->filters['to'])) {
            $from = Carbon::parse($this->filters['from'])->startOfDay();
            $to = Carbon::parse($this->filters['to'])->endOfDay();
            $query->whereBetween('date', [$from, $to]);
        } elseif (!empty($this->filters['from'])) {
            $query->whereDate('date', Carbon::parse($this->filters['from'])->toDateString());
        }

        // 🧩 Other filters
        if (!empty($this->filters['source'])) {
            $query->where('platform', 'like', "%{$this->filters['source']}%");
        }

        if (!empty($this->filters['bucket_id'])) {
            $query->where('lead_bucket_id', $this->filters['bucket_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('lead_status', $this->filters['status']);
        }

        if (!empty($this->filters['owner_id'])) {
            $query->where('owner_id', $this->filters['owner_id']);
        }

        if (!empty($this->filters['country'])) {
            $query->where('applying_country_for_a_visa', 'like', "%{$this->filters['country']}%");
        }

        if (!empty($this->filters['course'])) {
            $query->where('what_course_are_you_planning_to_study', 'like', "%{$this->filters['course']}%");
        }

        // ⚠️ Limit rows to prevent timeout
        $leads = $query->limit($this->maxRows)->get();

        // 🧠 Map leads to selected columns
        return $leads->map(function ($lead) {
            $row = [];

            foreach ($this->selectedColumns as $column) {
                switch ($column) {
                    case 'lead_id':
                        $row['Lead ID'] = $lead->id;
                        break;
                    case 'lead_name':
                        $row['Name'] = optional($lead->user)->name;
                        break;
                    case 'email':
                        $row['Email'] = optional($lead->user)->email;
                        break;
                    case 'contact_no':
                        $row['Contact'] = optional($lead->user)->contact_no;
                        break;
                    case 'bucket':
                        $row['Bucket'] = optional($lead->bucket)->name;
                        break;
                    case 'date':
                        $row['Date'] = $lead->date ? Carbon::parse($lead->date)->format('d-m-Y h:i A') : '';
                        break;
                    case 'status':
                        $row['Status'] = $lead->lead_status;
                        break;
                    case 'owner':
                        $row['Owner'] = optional($lead->owner)->name;
                        break;
                    case 'source':
                        $row['Source'] = $lead->platform;
                        break;
                    case 'country':
                        $row['Country'] = $lead->applying_country_for_a_visa;
                        break;
                    case 'course':
                        $row['Course'] = $lead->what_course_are_you_planning_to_study;
                        break;
                }
            }

            return $row;
        });
    }

    public function headings(): array
    {
        // Custom readable headers mapping
        $customHeadings = [
            'lead_id'   => 'Lead ID',
            'lead_name' => 'Name',  // fixed label here
            'email'     => 'Email',
            'contact_no'=> 'Contact',
            'date'      => 'Date',
            'bucket'    => 'Bucket',
            'status'    => 'Status',
            'owner'     => 'Owner',
            'source'    => 'Source',
            'country'   => 'Country',
            'course'    => 'Course',
        ];

        $headers = [];
        foreach ($this->selectedColumns as $col) {
            $headers[] = $customHeadings[$col] ?? ucwords(str_replace('_', ' ', $col));
        }

        return $headers;
    }

}

