import { useState } from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import axios from 'axios';

function WeatherIcon({ icon, condition }) {
    if (!icon) return <span className="text-3xl">🌫️</span>;
    return (
        <img
            src={`https://openweathermap.org/img/wn/${icon}@2x.png`}
            alt={condition}
            className="w-12 h-12"
        />
    );
}

function DayCard({ day }) {
    const date = new Date(day.date + 'T12:00:00');
    const label = date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
    const noData = day.temp_max === null;

    return (
        <div className="flex flex-col items-center bg-gray-50 rounded-lg p-4 border border-gray-200 min-w-[110px]">
            <p className="text-xs font-semibold text-gray-500 uppercase mb-1">{label}</p>
            <WeatherIcon icon={day.icon} condition={day.condition} />
            <p className="text-sm font-medium text-gray-700 mt-1">{day.condition}</p>
            {noData ? (
                <p className="text-xs text-gray-400 mt-1">No forecast</p>
            ) : (
                <>
                    <p className="text-sm font-bold text-gray-900 mt-1">
                        {day.temp_max}° / {day.temp_min}°C
                    </p>
                    <div className="flex gap-2 mt-1 text-xs text-gray-500">
                        <span>💧 {day.humidity}%</span>
                        <span>💨 {day.wind_speed} m/s</span>
                    </div>
                </>
            )}
        </div>
    );
}

export default function TravelPlanner({ auth }) {
    const [formData, setFormData] = useState({
        destination: '',
        travel_date: '',
        return_date: '',
    });
    const [dailyWeather, setDailyWeather] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError(null);
        setDailyWeather(null);

        try {
            const response = await axios.get('/api/travel/recommendations', {
                params: formData
            });
            setDailyWeather(response.data.daily_weather);
        } catch (err) {
            setError(err.response?.data?.error || err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Weather Checker" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div className="p-6">
                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div>
                                    <label htmlFor="destination" className="block text-sm font-medium text-gray-700">
                                        Destination
                                    </label>
                                    <input
                                        type="text"
                                        name="destination"
                                        id="destination"
                                        value={formData.destination}
                                        onChange={handleInputChange}
                                        placeholder="e.g. London, Tokyo, Paris"
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        required
                                    />
                                </div>

                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label htmlFor="travel_date" className="block text-sm font-medium text-gray-700">
                                            Travel Date
                                        </label>
                                        <input
                                            type="date"
                                            name="travel_date"
                                            id="travel_date"
                                            value={formData.travel_date}
                                            onChange={handleInputChange}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            required
                                        />
                                    </div>

                                    <div>
                                        <label htmlFor="return_date" className="block text-sm font-medium text-gray-700">
                                            Return Date
                                        </label>
                                        <input
                                            type="date"
                                            name="return_date"
                                            id="return_date"
                                            value={formData.return_date}
                                            onChange={handleInputChange}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            required
                                        />
                                    </div>
                                </div>

                                <div className="flex justify-end">
                                    <button
                                        type="submit"
                                        disabled={loading}
                                        className="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                                    >
                                        {loading ? 'Checking Weather...' : 'Check Weather'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {error && (
                        <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-red-700 text-sm">
                            {error}
                        </div>
                    )}

                    {dailyWeather && dailyWeather.length > 0 && (
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h3 className="text-xl font-semibold text-gray-900 mb-4">
                                    Weather forecast for {formData.destination}
                                </h3>
                                <div className="flex flex-wrap gap-3">
                                    {dailyWeather.map(day => (
                                        <DayCard key={day.date} day={day} />
                                    ))}
                                </div>
                                <p className="text-xs text-gray-400 mt-4">
                                    * OpenWeatherMap free tier provides up to 5 days of forecast. Days beyond that will show "No forecast".
                                </p>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
