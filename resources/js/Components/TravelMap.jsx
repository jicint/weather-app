import { useEffect, useRef } from 'react';
import { Loader } from '@googlemaps/js-api-loader';

export default function TravelMap({ location, markers = [] }) {
    const mapRef = useRef(null);
    const googleMapRef = useRef(null);

    useEffect(() => {
        const initMap = async () => {
            const loader = new Loader({
                apiKey: process.env.GOOGLE_MAPS_KEY,
                version: "weekly",
            });

            const google = await loader.load();
            const geocoder = new google.maps.Geocoder();

            geocoder.geocode({ address: location }, (results, status) => {
                if (status === "OK") {
                    const map = new google.maps.Map(mapRef.current, {
                        center: results[0].geometry.location,
                        zoom: 12,
                    });

                    googleMapRef.current = map;

                    // Add markers
                    markers.forEach(marker => {
                        new google.maps.Marker({
                            position: marker.position,
                            map: map,
                            title: marker.title
                        });
                    });
                }
            });
        };

        initMap();
    }, [location, markers]);

    return <div ref={mapRef} style={{ height: "400px", width: "100%" }} />;
} 