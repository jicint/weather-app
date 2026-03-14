<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Travel;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class TravelController extends Controller
{
    protected $weatherApiKey;

    public function __construct()
    {
        $this->weatherApiKey = config('services.weather.key');
    }

    public function getRecommendations(Request $request)
    {
        try {
            $validated = validator($request->all(), [
                'destination' => 'required|string',
                'travel_date' => 'required|date',
                'return_date' => 'required|date|after_or_equal:travel_date',
            ])->validate();

            $destination = trim($validated['destination']);
            $travelDate = new \DateTime($validated['travel_date']);
            $returnDate = new \DateTime($validated['return_date']);

            $dailyWeather = $this->getForecastByDay($destination, $travelDate, $returnDate);

            return response()->json([
                'daily_weather' => $dailyWeather
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getRecommendations: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getForecastByDay($destination, \DateTime $from, \DateTime $to)
    {
        $apiKey = env('OPENWEATHER_API_KEY');
        if (empty($apiKey)) {
            throw new \Exception('OpenWeather API key not configured');
        }

        $response = Http::get('http://api.openweathermap.org/data/2.5/forecast', [
            'q'       => $destination,
            'appid'   => $apiKey,
            'units'   => 'metric',
            'cnt'     => 40, // max 5 days of 3-hour steps
        ]);

        if (!$response->successful()) {
            throw new \Exception('Weather API error: ' . $response->status() . ' - ' . $response->body());
        }

        $data = $response->json();
        $grouped = [];

        foreach ($data['list'] as $entry) {
            $date = substr($entry['dt_txt'], 0, 10); // "YYYY-MM-DD"
            $entryDate = new \DateTime($date);

            if ($entryDate < $from || $entryDate > $to) {
                continue;
            }

            if (!isset($grouped[$date])) {
                $grouped[$date] = ['temps' => [], 'conditions' => [], 'humidity' => [], 'wind' => [], 'icons' => []];
            }

            $grouped[$date]['temps'][]      = $entry['main']['temp'];
            $grouped[$date]['humidity'][]   = $entry['main']['humidity'];
            $grouped[$date]['wind'][]       = $entry['wind']['speed'];
            $grouped[$date]['conditions'][] = $entry['weather'][0]['main'];
            $grouped[$date]['icons'][]      = $entry['weather'][0]['icon'];
        }

        $result = [];
        foreach ($grouped as $date => $values) {
            // Pick the most frequent condition and its icon
            $conditionCounts = array_count_values($values['conditions']);
            arsort($conditionCounts);
            $dominantCondition = array_key_first($conditionCounts);
            // Find icon matching the dominant condition
            $iconIndex = array_search($dominantCondition, $values['conditions']);
            $icon = $values['icons'][$iconIndex] ?? $values['icons'][0];

            $result[] = [
                'date'        => $date,
                'temp_min'    => round(min($values['temps'])),
                'temp_max'    => round(max($values['temps'])),
                'condition'   => $dominantCondition,
                'humidity'    => round(array_sum($values['humidity']) / count($values['humidity'])),
                'wind_speed'  => round(array_sum($values['wind']) / count($values['wind']), 1),
                'icon'        => $icon,
            ];
        }

        // If the date range extends beyond the 5-day forecast, fill remaining days with a note
        $current = clone $from;
        $allDates = [];
        while ($current <= $to) {
            $allDates[] = $current->format('Y-m-d');
            $current->modify('+1 day');
        }

        $forecastDates = array_column($result, 'date');
        foreach ($allDates as $d) {
            if (!in_array($d, $forecastDates)) {
                $result[] = [
                    'date'       => $d,
                    'temp_min'   => null,
                    'temp_max'   => null,
                    'condition'  => 'No forecast available',
                    'humidity'   => null,
                    'wind_speed' => null,
                    'icon'       => null,
                ];
            }
        }

        usort($result, fn($a, $b) => strcmp($a['date'], $b['date']));

        return $result;
    }

    public function getWeather($location, $date)
    {
        $response = Http::get("https://api.weatherapi.com/v1/forecast.json", [
            'key' => $this->weatherApiKey,
            'q' => $location,
            'dt' => $date
        ]);

        return response()->json($response->json());
    }

    private function generateRecommendations($source, $date, $familySize, $hasChildren)
    {
        // Add your recommendation logic here
        return [
            'transportation' => $this->getTransportationOptions($familySize),
            'activities' => $this->getActivities($hasChildren),
            'weather_info' => $this->getWeather($source, $date)
        ];
    }

    private function getTransportationOptions($destination, $familySize)
    {
        $destination = strtolower($destination);
        
        // City-specific transportation options
        $cityTransport = [
            'venice' => [
                'Water taxi',
                'Vaporetto (water bus)',
                'Walking',
                'Private water taxi'
            ],
            'paris' => [
                'Metro',
                'RER trains',
                'Bus',
                'Taxi',
                'Bike sharing'
            ],
            'london' => [
                'Underground (Tube)',
                'Double-decker bus',
                'Black cab',
                'River bus',
                'Bike sharing'
            ],
            'tokyo' => [
                'JR trains',
                'Metro',
                'Bus',
                'Taxi'
            ]
        ];

        // Get city-specific options or default options
        $baseOptions = $cityTransport[strtolower($destination)] ?? [
            'Local transport',
            'Taxi services',
            'Car rental',
            'Walking tours'
        ];

        // Add family-size specific recommendations
        if ($familySize >= 4) {
            $baseOptions[] = 'Private van service';
            $baseOptions[] = 'Family car rental';
        } elseif ($familySize <= 2) {
            $baseOptions[] = 'Bike rental';
            $baseOptions[] = 'Scooter sharing';
        }

        return array_slice($baseOptions, 0, 5); // Return up to 5 options
    }

    private function getActivities($destination, $hasChildren, $weather)
    {
        $destination = strtolower($destination);
        
        // City-specific activities
        $cityActivities = [
            'venice' => [
                'standard' => [
                    'Gondola ride',
                    'St. Mark\'s Basilica tour',
                    'Rialto Bridge visit',
                    'Murano glass blowing demo',
                    'Venetian art galleries'
                ],
                'children' => [
                    'Mask painting workshop',
                    'Gelato tasting tour',
                    'Pigeon feeding at St. Mark\'s',
                    'Venice Carnival Museum',
                    'Island hopping boat tour'
                ]
            ],
            'paris' => [
                'standard' => [
                    'Eiffel Tower visit',
                    'Louvre Museum tour',
                    'Seine River cruise',
                    'Notre-Dame Cathedral',
                    'Montmartre walk'
                ],
                'children' => [
                    'Disneyland Paris',
                    'Luxembourg Gardens',
                    'Natural History Museum',
                    'Aquarium de Paris',
                    'Chocolate making workshop'
                ]
            ],
            // Add more cities as needed
        ];

        // Get base activities
        $activities = isset($cityActivities[$destination]) 
            ? ($hasChildren ? $cityActivities[$destination]['children'] : $cityActivities[$destination]['standard'])
            : $this->getDefaultActivities($hasChildren);

        // Add weather-specific activities
        $weatherActivities = $this->getWeatherSpecificActivities($weather, $hasChildren);
        $activities = array_merge($activities, $weatherActivities);

        return array_slice(array_unique($activities), 0, 5); // Return up to 5 unique activities
    }

    private function getDefaultActivities($hasChildren)
    {
        return $hasChildren ? [
            'Local parks and playgrounds',
            'Interactive museums',
            'Zoo or aquarium visit',
            'Family-friendly tours',
            'Local entertainment centers'
        ] : [
            'Historical sites',
            'Local cuisine tours',
            'Cultural landmarks',
            'Art galleries',
            'Local markets'
        ];
    }

    private function getWeatherSpecificActivities($weather, $hasChildren)
    {
        $weather = strtolower($weather);
        
        $activities = [];
        
        switch ($weather) {
            case 'rain':
                $activities = $hasChildren ? [
                    'Indoor play centers',
                    'Science museums',
                    'Aquariums',
                    'Indoor workshops'
                ] : [
                    'Museum visits',
                    'Indoor markets',
                    'Art galleries',
                    'Local cafes'
                ];
                break;
                
            case 'clear':
                $activities = $hasChildren ? [
                    'Water parks',
                    'Outdoor playgrounds',
                    'Zoo visits',
                    'Park picnics'
                ] : [
                    'Walking tours',
                    'Outdoor cafes',
                    'Park visits',
                    'Photography tours'
                ];
                break;
                
            default:
                $activities = $hasChildren ? [
                    'Indoor-outdoor activities',
                    'Local attractions',
                    'Family entertainment'
                ] : [
                    'Local exploration',
                    'Cultural activities',
                    'City tours'
                ];
        }
        
        return $activities;
    }

    public function getDetailedWeather($location, $date)
    {
        $response = Http::get("https://api.weatherapi.com/v1/forecast.json", [
            'key' => $this->weatherApiKey,
            'q' => $location,
            'dt' => $date,
            'days' => 7 // Get a week's forecast
        ]);

        return response()->json([
            'current' => $response['current'],
            'forecast' => $response['forecast'],
            'alerts' => $response['alerts'] ?? null,
            'air_quality' => $response['current']['air_quality'] ?? null
        ]);
    }

    public function createBooking(Request $request)
    {
        $validated = $request->validate([
            'travel_id' => 'required|exists:travels,id',
            'booking_type' => 'required|in:accommodation,transportation,activity',
            'provider' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'cost' => 'required|numeric',
            'details' => 'required|array'
        ]);

        $booking = Booking::create([
            ...$validated,
            'user_id' => auth()->id(),
            'status' => 'confirmed',
            'booking_reference' => uniqid('BK-')
        ]);

        return response()->json($booking, 201);
    }

    public function getTravelHistory()
    {
        try {
            // Check if user is authenticated
            if (!auth()->check()) {
                \Log::error('User not authenticated in getTravelHistory');
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            \Log::info('Getting travel history for user: ' . auth()->id());
            
            // Check if travels table exists
            if (!Schema::hasTable('travels')) {
                \Log::error('Travels table does not exist');
                return response()->json(['error' => 'Database table not found'], 500);
            }

            $history = Travel::where('user_id', auth()->id())
                ->with('bookings')
                ->orderBy('travel_date', 'desc')
                ->get();

            return response()->json($history);
        } catch (\Exception $e) {
            \Log::error('Error in getTravelHistory: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleFavorite($id)
    {
        $travel = Travel::findOrFail($id);
        $travel->is_favorite = !$travel->is_favorite;
        $travel->save();

        return response()->json(['is_favorite' => $travel->is_favorite]);
    }

    public function getTravelTips($destination)
    {
        try {
            // Clean the destination name
            $destination = trim(strtolower($destination));
            
            // Get real-time weather data
            $weatherData = $this->getWeatherData($destination);
            
            // Get destination info from Wikipedia API
            $destinationInfo = $this->getDestinationInfo($destination);
            
            // Build dynamic tips based on weather and destination data
            $tips = [
                'destination_info' => $destinationInfo,
                'current_weather' => $weatherData,
                'best_time' => $this->getBestTimeToVisit($destination, $weatherData),
                'local_tips' => $this->getLocalTips($destination, $weatherData),
                'weather_notes' => $this->getWeatherNotes($weatherData),
                'practical_tips' => $this->getPracticalTips($destination, $weatherData)
            ];

            return response()->json($tips);

        } catch (\Exception $e) {
            \Log::error('Error getting travel tips for ' . $destination . ': ' . $e->getMessage());
            return response()->json([
                'error' => 'Unable to fetch travel tips',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    private function getWeatherData($destination)
    {
        try {
            $apiKey = env('OPENWEATHER_API_KEY');
            if (empty($apiKey)) {
                throw new \Exception('OpenWeather API key not configured');
            }

            $url = "http://api.openweathermap.org/data/2.5/weather?q={$destination}&appid={$apiKey}&units=metric";
            
            $response = Http::get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'temp_c' => round($data['main']['temp']),
                    'condition' => $data['weather'][0]['main'],
                    'description' => $data['weather'][0]['description'],
                    'humidity' => $data['main']['humidity'],
                    'wind_speed' => $data['wind']['speed']
                ];
            }
            
            throw new \Exception('Weather API request failed: ' . $response->status());
        } catch (\Exception $e) {
            \Log::error('Weather API error: ' . $e->getMessage());
            return [
                'temp_c' => null,
                'condition' => 'Weather data unavailable',
                'description' => 'Unable to fetch weather information',
                'humidity' => null,
                'wind_speed' => null
            ];
        }
    }

    private function getDestinationInfo($destination)
    {
        try {
            // Wikipedia API endpoint
            $url = "https://en.wikipedia.org/api/rest_v1/page/summary/" . urlencode($destination);
            
            $response = Http::get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'description' => $data['extract'] ?? 'Information not available',
                    'url' => $data['content_urls']['desktop']['page'] ?? null
                ];
            }
            
            return ['description' => 'Information not available'];
        } catch (\Exception $e) {
            \Log::error('Wikipedia API error: ' . $e->getMessage());
            return ['description' => 'Information not available'];
        }
    }

    private function getBestTimeToVisit($destination, $weatherData)
    {
        // Use weather data to provide dynamic recommendations
        $currentTemp = $weatherData['temp_c'] ?? 20;
        $currentCondition = $weatherData['condition'] ?? '';
        
        $recommendations = [];
        
        // Temperature-based recommendations
        if ($currentTemp < 10) {
            $recommendations[] = "Currently cold ({$currentTemp}°C). Best to visit in summer months.";
        } elseif ($currentTemp > 30) {
            $recommendations[] = "Currently very warm ({$currentTemp}°C). Spring or Fall might be more comfortable.";
        } else {
            $recommendations[] = "Current temperature is pleasant ({$currentTemp}°C). Good time to visit.";
        }
        
        // Weather condition recommendations
        if (stripos($currentCondition, 'rain') !== false) {
            $recommendations[] = "Currently experiencing rain. Pack appropriate gear.";
        } elseif (stripos($currentCondition, 'clear') !== false) {
            $recommendations[] = "Clear weather conditions. Great for outdoor activities.";
        }
        
        return $recommendations;
    }

    private function getLocalTips($destination, $weatherData)
    {
        $tips = [];
        
        // Weather-based tips
        if (isset($weatherData['temp_c'])) {
            if ($weatherData['temp_c'] > 25) {
                $tips[] = "Stay hydrated and bring sun protection";
                $tips[] = "Plan outdoor activities for morning or evening";
            } elseif ($weatherData['temp_c'] < 10) {
                $tips[] = "Dress in warm layers";
                $tips[] = "Check indoor attractions for cold weather";
            }
        }
        
        // Add some general tips
        $tips[] = "Research local transportation options";
        $tips[] = "Check local events calendar";
        $tips[] = "Look for local markets and authentic restaurants";
        
        return $tips;
    }

    private function getWeatherNotes($weatherData)
    {
        if (!$weatherData) {
            return ["Weather information currently unavailable"];
        }
        
        $notes = [];
        $temp = $weatherData['temp_c'];
        $condition = $weatherData['condition'];
        $humidity = $weatherData['humidity'];
        
        $notes[] = "Current temperature: {$temp}°C";
        $notes[] = "Weather condition: {$condition}";
        $notes[] = "Humidity: {$humidity}%";
        
        // Add specific recommendations based on conditions
        if ($temp > 30) {
            $notes[] = "High temperature - stay hydrated and avoid midday sun";
        } elseif ($temp < 10) {
            $notes[] = "Cold conditions - dress warmly and check indoor activity options";
        }
        
        if ($humidity > 70) {
            $notes[] = "High humidity - plan for indoor breaks";
        }
        
        return $notes;
    }

    private function getPracticalTips($destination, $weatherData)
    {
        $tips = [
            "Download offline maps for {$destination}",
            "Save emergency numbers",
            "Check visa requirements"
        ];
        
        // Add weather-specific practical tips
        if ($weatherData) {
            if ($weatherData['temp_c'] > 25) {
                $tips[] = "Pack light, breathable clothing";
                $tips[] = "Bring sunscreen and a hat";
            } elseif ($weatherData['temp_c'] < 15) {
                $tips[] = "Pack warm clothing and layers";
                $tips[] = "Check heating in accommodation";
            }
            
            if (stripos($weatherData['condition'], 'rain') !== false) {
                $tips[] = "Bring waterproof clothing and umbrella";
                $tips[] = "Have indoor backup plans";
            }
        }
        
        return $tips;
    }
} 