export default function TravelRecommendations({ recommendations }) {
    if (!recommendations) return null;

    return (
        <div className="space-y-6">
            {recommendations.transportation && (
                <div className="bg-white p-4 rounded-lg shadow">
                    <h4 className="text-lg font-medium text-gray-900 mb-2">Transportation Options</h4>
                    <ul className="list-disc pl-5">
                        {recommendations.transportation.map((option, index) => (
                            <li key={index} className="text-gray-600">{option}</li>
                        ))}
                    </ul>
                </div>
            )}

            {recommendations.activities && (
                <div className="bg-white p-4 rounded-lg shadow mt-4">
                    <h4 className="text-lg font-medium text-gray-900 mb-2">Recommended Activities</h4>
                    <ul className="list-disc pl-5">
                        {recommendations.activities.map((activity, index) => (
                            <li key={index} className="text-gray-600">{activity}</li>
                        ))}
                    </ul>
                </div>
            )}

            {recommendations.weather_info && (
                <div className="bg-white p-4 rounded-lg shadow mt-4">
                    <h4 className="text-lg font-medium text-gray-900 mb-2">Weather Information</h4>
                    <div className="text-gray-600">
                        <p>Temperature: {recommendations.weather_info.temp_c}°C</p>
                        <p>Condition: {recommendations.weather_info.condition}</p>
                    </div>
                </div>
            )}
        </div>
    );
} 