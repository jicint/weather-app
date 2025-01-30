# Weather Checker Application

A Laravel-based web application that provides real-time weather information for travel destinations.

## Features

- Real-time weather data using OpenWeatherMap API
- Temperature, conditions, humidity, and wind speed information
- Date-based travel planning
- Clean and responsive user interface

## Requirements

- PHP 8.1 or higher
- Composer
- Node.js & NPM
- Laravel 10.x
- OpenWeatherMap API key

## Installation

1. Clone the repository

2. Install PHP dependencies

3. Install NPM dependencies

```bash
npm install
```

4. Create environment file
```bash
cp .env.example .env
```

5. Generate application key
```bash
php artisan key:generate
```

6. Configure your OpenWeatherMap API key in .env
```
OPENWEATHER_API_KEY=your_api_key_here
```

7. Run migrations
```bash
php artisan migrate
```

## Development

1. Start the Laravel development server
```bash
php artisan serve
```

2. Start the Vite development server
```bash
npm run dev
```

## Usage

1. Register/Login to the application
2. Enter your destination
3. Select travel dates
4. View detailed weather information

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.