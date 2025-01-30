import { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import TravelRecommendations from '@/Components/TravelRecommendations';
import axios from 'axios';

export default function TravelPlanner({ auth }) {
    const [formData, setFormData] = useState({
        destination: '',
        travel_date: '',
        return_date: '',
    });
    const [recommendations, setRecommendations] = useState(null);
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            const response = await axios.get('/api/travel/recommendations', {
                params: formData
            });
            setRecommendations(response.data);
        } catch (error) {
            console.error('Error:', error);
            alert(`Error: ${error.response?.data?.error || error.message}`);
        } finally {
            setLoading(false);
        }
    };

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
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
                                        className="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    >
                                        {loading ? 'Checking Weather...' : 'Check Weather'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {recommendations && (
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div className="p-6">
                                <h3 className="text-xl font-semibold text-gray-900 mb-4">
                                    Weather in {formData.destination}
                                </h3>
                                
                                {recommendations.weather_info && (
                                    <div className="space-y-2 text-gray-600">
                                        <p>Temperature: {recommendations.weather_info.temp_c}°C</p>
                                        <p>Condition: {recommendations.weather_info.condition}</p>
                                        <p>Description: {recommendations.weather_info.description}</p>
                                        <p>Humidity: {recommendations.weather_info.humidity}%</p>
                                        <p>Wind Speed: {recommendations.weather_info.wind_speed} m/s</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 