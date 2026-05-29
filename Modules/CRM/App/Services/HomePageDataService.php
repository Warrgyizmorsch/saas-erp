<?php

namespace Modules\CRM\App\Services;

class HomePageDataService
{
    public function getCountries(): array
    {
        return [
            'uk' => [
                'name' => 'United Kingdom',
                'image' => 'images/country/ukhome.webp',
                'thumb' => 'images/country/ukhome.webp',
                'features' => [
                    'World-Class Education',
                    'Rich academic heritage',
                    'Global exposure',
                    'Fast-track student visas',
                    'Strong alumni network',
                    'High employability',
                ],
                'subtittle' => 'World-Class Education',
                'route' => '/country/study-in-uk',
            ],
            'usa' => [
                'name' => 'USA',
                'image' => 'images/country/usahome.webp',
                'thumb' => 'images/country/usahome.webp',
                'features' => [
                    'Top-ranked institutions',
                    'Flexible education system',
                    'Global career exposure',
                    'Strong research culture',
                    'High-paying job market',
                    'Powerful alumni network',
                ],
                'subtittle' => 'Global Education Leader',
                'route' => '/country/study-in-usa',
            ],
            'france' => [
                'name' => 'France',
                'image' => 'images/country/francehome.webp',
                'thumb' => 'images/country/francehome.webp',
                'features' => [
                    'Affordable education options',
                    'Renowned public universities',
                    'Innovation & research focus',
                    'Rich cultural exposure',
                    'EU-wide career access',
                    'Scholarships for internationals',
                ],
                'route' => '/country/study-in-france',
                'subtittle' => 'Affordable EU Education',
            ],
            'australia' => [
                'name' => 'Australia',
                'image' => 'images/country/australiahome.webp',
                'thumb' => 'images/country/australiahome.webp',
                'features' => [
                    'Internationally accredited universities',
                    'Work-study balance',
                    'High living standards',
                    'Post-study work rights',
                    'Industry-relevant courses',
                    'Strong employability outcomes',
                ],
                'route' => '/country/study-in-australia',
                'subtittle' => 'Quality Education Hub',
            ],
            'germany' => [
                'name' => 'Germany',
                'image' => 'images/country/germanyhome.webp',
                'thumb' => 'images/country/germanyhome.webp',
                'features' => [
                    'No or low tuition fees',
                    'Engineering & technology leader',
                    'Strong industrial partnerships',
                    'High employability rates',
                    'Work opportunities during study',
                    'EU-wide career access',
                ],
                'route' => '/country/study-in-germany',
                'subtittle' => 'Tuition-Free Education',
            ],
            'canada' => [
                'name' => 'Canada',
                'image' => 'images/country/canadahome.webp',
                'thumb' => 'images/country/canadahome.webp',
                'features' => [
                    'Globally recognised degrees',
                    'Student-friendly immigration',
                    'Work while studying options',
                    'Safe & welcoming country',
                    'Post-study work visas',
                    'High quality of life',
                ],
                'route' => '/country/study-in-canada',
                'subtittle' => 'Study-Work-Settle',
            ],
            'dubai' => [
                'name' => 'Dubai',
                'image' => 'images/country/uaehome.webp',
                'thumb' => 'images/country/uaehome.webp',
                'features' => [
                    'Global business destination',
                    'Career-focused education',
                    'International universities campuses',
                    'No income tax benefits',
                    'Strong industry exposure',
                    'Fast-growing job market',
                ],
                'route' => '/country/study-in-uae',
                'subtittle' => 'Global Career Hub',
            ],
            'ireland' => [
                'name' => 'Ireland',
                'image' => 'images/country/irelandhome.webp',
                'thumb' => 'images/country/irelandhome.webp',
                'features' => [
                    'Global tech hub',
                    'Home to top MNCs',
                    'English-speaking country',
                    'Post-study work options',
                    'Research-driven education',
                    'Strong job prospects',
                ],
                'route' => '/country/study-in-ireland',
                'subtittle' => 'Tech & Innovation Hub',
            ],
            'italy' => [
                'name' => 'Italy',
                'image' => 'images/country/italyhome.webp',
                'thumb' => 'images/country/italyhome.webp',
                'features' => [
                    'Globally recognised universities',
                    'Excellence in design & architecture',
                    'Affordable education costs',
                    'Rich academic history',
                    'EU-wide career opportunities',
                    'Scholarships for international students',
                ],
                'route' => '/country/study-in-italy',
                'subtittle' => 'Design & Innovation',
            ],
            'netherland' => [
                'name' => 'Netherland',
                'image' => 'images/country/netherlandhome.webp',
                'thumb' => 'images/country/netherlandhome.webp',
                'features' => [
                    'Wide range of English programs',
                    'Research-oriented education',
                    'Innovative teaching methods',
                    'Strong global rankings',
                    'Excellent work-life balance',
                    'High employability in EU',
                ],
                'route' => '/country/study-in-netherlands',
                'subtittle' => 'English-Taught Programs',
            ],
            'newzealand' => [
                'name' => 'New Zealand',
                'image' => 'images/country/newzealandhome.webp',
                'thumb' => 'images/country/newzealandhome.webp',
                'features' => [
                    'World-class education standards',
                    'Excellence in hospitality & management',
                    'Strong research ecosystem',
                    'High-paying job market',
                    'Multilingual environment',
                    'Global career recognitionn',
                ],
                'route' => '/country/study-in-new-zealand',
                'subtittle' => 'Safe Study Destination',
            ],
            'singapore' => [
                'name' => 'Singapore',
                'image' => 'images/country/singaporehome.webp',
                'thumb' => 'images/country/singaporehome.webp',
                'features' => [
                    'Asia’s education hub',
                    'Globally ranked universities',
                    'Strong industry exposure',
                    'Short-duration courses',
                    'Excellent infrastructure',
                    'High career growth potential',
                ],
                'route' => '/country/study-in-singapore',
                'subtittle' => 'Asia’s Education Hub',
            ],
            'spain' => [
                'name' => 'Spain',
                'image' => 'images/country/spainhome.webp',
                'thumb' => 'images/country/spainhome.webp',
                'features' => [
                    'Affordable tuition fees',
                    'Popular EU study destination',
                    'English-taught programs available',
                    'Rich cultural experience',
                    'Growing international universities',
                    'Access to European job market',
                ],
                'route' => '/country/study-in-spain',
                'subtittle' => 'Affordable EU Studies',
            ],
            'switzerland' => [
                'name' => 'Switzerland',
                'image' => 'images/country/switzerlandhome.webp',
                'thumb' => 'images/country/switzerlandhome.webp',
                'features' => [
                    'World-class education standards',
                    'Excellence in hospitality & management',
                    'Strong research ecosystem',
                    'High-paying job market',
                    'Multilingual environment',
                    'Global career recognition',
                ],
                'route' => '/country/study-in-switzerland',
                 'subtittle' => 'Excellence in Education',
            ],
        ];
    }

    public function getTestimonials(): array
    {
        return [
            (object) ['url' => 'https://www.youtube.com/shorts/Izz1Wrb0lIA', 'title' => 'Ayushi Review', 'image' => asset('new-home-images/Ayushi.webp')],
            (object) ['url' => 'https://www.youtube.com/shorts/LWtrbwebgvk', 'title' => 'Prakhar Review', 'image' => asset('new-home-images/Prakhar.webp')],
            (object) ['url' => 'https://www.youtube.com/shorts/Kf-akkSWaeo', 'title' => 'Amit Zala', 'image' => asset('new-home-images/Amit.webp')],
            (object) ['url' => 'https://www.youtube.com/shorts/eI4pM2c9Yxg', 'title' => 'Sumandeep Review', 'image' => asset('new-home-images/Sumandeep.webp')],
            (object) ['url' => 'https://www.youtube.com/shorts/Otkbyy93KeQ', 'title' => 'Deepali Review', 'image' => asset('new-home-images/Deepali.webp')],
            (object) ['url' => 'https://www.youtube.com/shorts/BgGml15UssU', 'title' => 'Abhishek Review', 'image' => asset('new-home-images/Abhishek.webp')],
            (object) ['url' => 'https://www.youtube.com/shorts/sZoeyhrQFc8', 'title' => 'Abid Review', 'image' => asset('new-home-images/Abid.webp')],
            (object) ['url' => 'https://www.youtube.com/shorts/RRVooWNDhCQ', 'title' => 'Sajay', 'image' => asset('new-home-images/Sajay.webp')],
            (object) ['url' => 'https://www.youtube.com/shorts/m4CCvGqcDD0', 'title' => 'Shubham Review', 'image' => asset('new-home-images/Shubham.webp')],
            (object) ['url' => 'https://www.youtube.com/shorts/P4bsm9o-4ro', 'title' => 'Soundarya Review', 'image' => asset('new-home-images/Soundarya.webp')],
            (object) ['url' => 'https://www.youtube.com/shorts/5ywBBjfkWwk', 'title' => 'Jasmine Review', 'image' => asset('new-home-images/Jasmine.webp')],
            (object) ['url' => 'https://www.youtube.com/shorts/RH8nEhgewig', 'title' => 'Kishore Review', 'image' => asset('new-home-images/Kishore.webp')],
            (object) ['url' => 'https://www.youtube.com/shorts/FhdcrdpR0iM', 'title' => 'Arooj Review', 'image' => asset('new-home-images/Arooj.webp')],
        ];
    }

}

